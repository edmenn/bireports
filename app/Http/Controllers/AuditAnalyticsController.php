<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Report;
use App\Models\User;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

use Symfony\Component\HttpFoundation\StreamedResponse;


// PhpSpreadsheet (librería completa de Excel)
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

final class AuditAnalyticsController extends Controller
{
    /** ÚNICA Acción que cuenta como “acceso”. Según tu dump real. */
    private const ACCESS_ACTIONS = ['render','view'];

    public function index(Request $request): View
    {
        $data = $this->composeViewData($request);
        return view('auditoria.index', $data);
    }

    /**
     * Build analytics payload reused by both the HTML view and the export.
     */
    private function composeViewData(Request $request, bool $loadAllTabs = false): array
    {
        $tab = (string) $request->input('tab', 'dashboard');

        $appTz = \App\Support\AppTimezone::current() ?? config('app.timezone');

        $to = now()->utc()->endOfDay();
        $from = (clone $to)->subDays(59)->startOfDay();

        if ($request->filled('from')) {
            $from = Carbon::parse((string) $request->input('from'), $appTz)
                ->utc()->startOfDay();
        }
        if ($request->filled('to')) {
            $to = Carbon::parse((string) $request->input('to'), $appTz)
                ->utc()->endOfDay();
        }

        $applyUserFilter = ($tab === 'user-profile');
        $userId = ($applyUserFilter && $request->filled('user_id'))
            ? (int) $request->input('user_id')
            : null;

        $baseGlobal = Audit::query()
            ->whereBetween('occurred_at', [$from, $to])
            ->where('action', 'render');

        $baseScoped = (clone $baseGlobal)
            ->when($applyUserFilter && $userId, fn ($q) => $q->where('user_id', $userId));

        $usersList = User::query()->select('id', 'name', 'email')->orderBy('name')->limit(1000)->get();

        $totals = [
            'totalAccesos'  => (clone $baseScoped)->count(),
            'totalUsuarios' => (clone $baseScoped)->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            'totalReportes' => (clone $baseScoped)->distinct('report_uuid')->count('report_uuid'),
        ];

        $dashboard = $this->buildDashboard($baseGlobal, $from, $to);

        $usersMost   = [];
        $usersLeast  = [];
        $usersNoAct  = [];

        $reportsMost  = [];
        $reportsLeast = [];
        $reportsNoAcc = [];

        $userProfile = null;
        $userEvents  = [];

        $tabsToHydrate = $loadAllTabs
            ? ['users-most','users-least','users-inactive','reports-most','reports-least','reports-inactive','user-profile']
            : [$tab];

        foreach (array_unique($tabsToHydrate) as $activeTab) {
            switch ($activeTab) {
                case 'users-most':
                    $usersMost = $this->usersRank($baseGlobal, 'desc', 100);
                    break;
                case 'users-least':
                    $usersLeast = $this->usersRank($baseGlobal, 'asc', 100);
                    break;
                case 'users-inactive':
                    $usersNoAct = $this->usersWithoutAccess($from, $to);
                    break;
                case 'reports-most':
                    $reportsMost = $this->reportsRank($baseGlobal, 'desc', 100);
                    break;
                case 'reports-least':
                    $reportsLeast = $this->reportsRank($baseGlobal, 'asc', 100);
                    break;
                case 'reports-inactive':
                    $reportsNoAcc = $this->reportsWithoutAccess($from, $to);
                    break;
                case 'user-profile':
                    if ($userId) {
                        [$userProfile, $userEvents] = $this->buildUserProfile($userId, $from, $to);
                    }
                    break;
            }
        }

        $dowLabels = [1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes',6=>'Sábado',7=>'Domingo'];

        return array_merge([
            'tab'          => $tab,
            'from'         => $from->toDateString(),
            'to'           => $to->toDateString(),
            'userId'       => $userId,
            'usersList'    => $usersList,
            'dowLabels'    => $dowLabels,
            'accessAction' => 'render+view',
            'exportMeta'   => [
                'fromUtc'          => clone $from,
                'toUtc'            => clone $to,
                'displayTimezone'  => $appTz,
                'userId'           => $userId,
                'tab'              => $tab,
            ],
        ], $totals, $dashboard, [
            'usersMost'    => $usersMost,
            'usersLeast'   => $usersLeast,
            'usersNoAct'   => $usersNoAct,
            'reportsMost'  => $reportsMost,
            'reportsLeast' => $reportsLeast,
            'reportsNoAcc' => $reportsNoAcc,
            'userProfile'  => $userProfile,
            'userEvents'   => $userEvents,
        ]);
    }

    /** Dashboard global (renders). */
    private function buildDashboard($base, Carbon $from, Carbon $to): array
    {
        $tz = addslashes(\App\Support\AppTimezone::current() ?? config('app.timezone'));
        $displayTz = \App\Support\AppTimezone::current() ?? config('app.timezone');

        $monthsRaw = (clone $base)
            ->selectRaw("to_char(date_trunc('month', occurred_at AT TIME ZONE '$tz'), 'YYYY-MM') AS ym, COUNT(*) AS total")
            ->groupBy('ym')->orderBy('ym')->get()->keyBy('ym');

        $months = [];
        $cursor = (clone $from)->startOfMonth();
        $end    = (clone $to)->startOfMonth();
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m');
            $months[] = [
                'label' => $cursor->translatedFormat('F'),
                'total' => isset($monthsRaw[$key]) ? (int)$monthsRaw[$key]->total : 0,
            ];
            $cursor->addMonth();
        }

        $gridRaw = (clone $base)
            ->selectRaw("EXTRACT(ISODOW FROM (occurred_at AT TIME ZONE '$tz'))::int AS dow, EXTRACT(HOUR FROM (occurred_at AT TIME ZONE '$tz'))::int AS hr, COUNT(*) AS total")
            ->groupBy('dow','hr')->get();

        $heatmap = [];
        for ($h = 0; $h < 24; $h++) {
            $row = ['hour' => sprintf('%02d:00', $h)];
            for ($d = 1; $d <= 7; $d++) $row[$d] = 0;
            $heatmap[$h] = $row;
        }
        foreach ($gridRaw as $r) $heatmap[$r->hr][$r->dow] = (int)$r->total;

        $dailyRaw = (clone $base)
            ->selectRaw("DATE(occurred_at AT TIME ZONE '$tz') AS d, COUNT(*) AS total")
            ->groupBy('d')->orderBy('d')->get()->keyBy('d');

        $daily = [];
        $cur = (clone $from)->startOfDay();
        while ($cur->lte($to)) {
            $k = $cur->toDateString();
            $daily[] = ['date'=>$k, 'total'=> isset($dailyRaw[$k]) ? (int)$dailyRaw[$k]->total : 0];
            $cur->addDay();
        }

        $topUsers = (clone $base)
            ->select('user_id', DB::raw('COUNT(*) AS total'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')->orderByDesc('total')->limit(10)->get()
            ->map(function ($r) {
                $u = User::query()->find($r->user_id);
                return ['name' => $u?->name ?? ('Usuario #'.$r->user_id), 'total' => (int)$r->total];
            })->values();

        $topReportsRows = (clone $base)
            ->select('report_uuid', DB::raw('COUNT(*) AS total'))
            ->groupBy('report_uuid')->orderByDesc('total')->limit(10)->get();

        $reportNames = Report::query()->whereIn('id', $topReportsRows->pluck('report_uuid'))->pluck('name','id');
        $topReports  = $topReportsRows->map(function ($r) use ($reportNames) {
            $id = (string)$r->report_uuid;
            return ['label' => $reportNames[$id] ?? $id, 'total' => (int)$r->total];
        })->values();

        $since7  = (clone $to)->copy()->subDays(6)->startOfDay();
        $since30 = (clone $to)->copy()->subDays(29)->startOfDay();

        $activeUsers7   = (clone $base)->where('occurred_at','>=',$since7 )->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $activeUsers30  = (clone $base)->where('occurred_at','>=',$since30)->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $activeReports7 = (clone $base)->where('occurred_at','>=',$since7 )->distinct('report_uuid')->count('report_uuid');
        $activeReports30= (clone $base)->where('occurred_at','>=',$since30)->distinct('report_uuid')->count('report_uuid');

        $errors7    = Audit::query()->whereBetween('occurred_at', [$since7, $to])->where('action','error')->count();
        $views7     = (clone $base)->where('occurred_at','>=',$since7)->count();
        $errorRate7 = $views7 > 0 ? round(($errors7 / $views7) * 100, 1) : 0.0;

        $topErrorReportsRows = Audit::query()
            ->whereBetween('occurred_at', [$since30, $to])
            ->where('action','error')
            ->select('report_uuid', DB::raw('COUNT(*) AS total'))
            ->groupBy('report_uuid')->orderByDesc('total')->limit(10)->get();

        $errReportNames = Report::query()->whereIn('id', $topErrorReportsRows->pluck('report_uuid'))->pluck('name','id');
        $topErrorReports = $topErrorReportsRows->map(function($r) use ($errReportNames){
            $id = (string)$r->report_uuid;
            return ['label'=>$errReportNames[$id] ?? $id, 'total'=>(int)$r->total];
        })->values();

        $since7_from = $since7;
        $prev7_from = (clone $since7_from)->subDays(7);
        $prev7_to   = (clone $since7_from)->subDay()->endOfDay();

        $cnt7  = (clone $base)->whereBetween('occurred_at', [$since7, $to])->count();
        $cntp7 = (clone $base)->whereBetween('occurred_at', [$prev7_from, $prev7_to])->count();
        $delta7 = $cntp7 > 0 ? round((($cnt7 - $cntp7) / $cntp7) * 100, 1) : null;

        $alerts = [];
        if ($delta7 !== null && $delta7 <= -30.0) {
            $alerts[] = ['type'=>'warning','msg'=>"Caída de uso ≥30%: Ultimos 7 días ($cnt7) vs previos ($cntp7) → {$delta7}%."]; }
        if ($errorRate7 >= 5.0 && $errors7 >= 5) {
            $alerts[] = ['type'=>'danger','msg'=>"Tasa de errores alta en 7 días: {$errorRate7}% ({$errors7} errores / {$views7} accesos)."];
        }

        return [
            'months'         => $months,
            'topUsers'       => $topUsers,
            'topReports'     => $topReports,
            'heatmap'        => array_values($heatmap),
            'daily'          => $daily,
            'activeUsers7'   => $activeUsers7,
            'activeUsers30'  => $activeUsers30,
            'activeReports7' => $activeReports7,
            'activeReports30'=> $activeReports30,
            'errors7'        => $errors7,
            'errorRate7'     => $errorRate7,
            'topErrorReports'=> $topErrorReports,
            'delta7'         => $delta7,
            'alerts'         => $alerts,
        ];
    }

    /** Rankings Usuarios (renders). */
    private function usersRank($base, string $direction, int $limit): array
    {
        $displayTz = \App\Support\AppTimezone::current() ?? config('app.timezone');
        $tz = addslashes(\App\Support\AppTimezone::current() ?? config('app.timezone'));
        $rows = (clone $base)
            ->select('user_id', DB::raw('COUNT(*) AS total'), DB::raw("MAX(occurred_at AT TIME ZONE '$tz') AS last_access_local"))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderBy('total', $direction === 'asc' ? 'asc' : 'desc')
            ->limit($limit)->get();

        return $rows->map(function ($r) use ($displayTz) {
            $u = User::query()->find($r->user_id);
            return [
                'user_id'     => (int) $r->user_id,
                'name'        => $u?->name ?? ('Usuario #'.$r->user_id),
                'email'       => $u?->email,
                'total'       => (int)$r->total,
                'last_access' => $r->last_access_local ?? null,
                'created_at'  => $u?->created_at ? ( ($u->created_at instanceof Carbon ? $u->created_at->copy() : Carbon::parse($u->created_at)) )->setTimezone($displayTz)->toDateTimeString() : null,
            ];
        })->values()->all();
    }

    /** Usuarios sin renders en rango. */
    private function usersWithoutAccess(Carbon $from, Carbon $to): array
    {
        $displayTz = \App\Support\AppTimezone::current() ?? config('app.timezone');
        $tz = addslashes(\App\Support\AppTimezone::current() ?? config('app.timezone'));
        $sub = Audit::query()
            ->select('user_id')
            ->whereNotNull('user_id')
            ->whereBetween('occurred_at', [$from, $to])
            ->where('action', 'render')
            ->groupBy('user_id');

        $users = User::query()
            ->whereNotIn('id', $sub)
            ->orderBy('created_at', 'desc')
            ->limit(500)->get();

        $lastByUser = Audit::query()
            ->select('user_id', DB::raw("MAX(occurred_at AT TIME ZONE '$tz') AS last_access_local"))
            ->whereNotNull('user_id')
            ->where('action', 'render')
            ->groupBy('user_id')->pluck('last_access_local', 'user_id');

        return $users->map(function ($u) use ($lastByUser, $displayTz) {
            return [
                'user_id'     => (int)$u->id,
                'name'        => $u->name,
                'email'       => $u->email,
                'last_access' => $lastByUser[$u->id] ?? null,
                'created_at'  => $u->created_at ? ( ($u->created_at instanceof Carbon ? $u->created_at->copy() : Carbon::parse($u->created_at)) )->setTimezone($displayTz)->toDateTimeString() : null,
            ];
        })->values()->all();
    }

    /** Rankings Reportes (renders). */
    private function reportsRank($base, string $direction, int $limit): array
    {
        $displayTz = \App\Support\AppTimezone::current() ?? config('app.timezone');
        $tz = addslashes(\App\Support\AppTimezone::current() ?? config('app.timezone'));
        $rows = (clone $base)
            ->select('report_uuid', DB::raw('COUNT(*) AS total'), DB::raw("MAX(occurred_at AT TIME ZONE '$tz') AS last_access_local"))
            ->groupBy('report_uuid')
            ->orderBy('total', $direction === 'asc' ? 'asc' : 'desc')
            ->limit($limit)->get();

        $names = Report::query()->whereIn('id', $rows->pluck('report_uuid'))->pluck('name', 'id');

        return $rows->map(function ($r) use ($names, $displayTz) {
            $id = (string)$r->report_uuid;
            return [
                'report_uuid' => $id,
                'name'        => $names[$id] ?? $id,
                'total'       => (int)$r->total,
                'last_access' => $r->last_access_local ?? null,
            ];
        })->values()->all();
    }

    /** Reportes sin renders en rango. */
    private function reportsWithoutAccess(Carbon $from, Carbon $to): array
    {
        $displayTz = \App\Support\AppTimezone::current() ?? config('app.timezone');
        $tz = addslashes(\App\Support\AppTimezone::current() ?? config('app.timezone'));
        $sub = Audit::query()
            ->select('report_uuid')
            ->whereBetween('occurred_at', [$from, $to])
            ->where('action', 'render')
            ->groupBy('report_uuid');

        $reports = Report::query()
            ->whereNotIn('id', $sub)
            ->orderBy('created_at', 'desc')
            ->limit(500)->get(['id','name','created_at']);

        $lastByReport = Audit::query()
            ->select('report_uuid', DB::raw("MAX(occurred_at AT TIME ZONE '$tz') AS last_access_local"))
            ->where('action', 'render')
            ->groupBy('report_uuid')->pluck('last_access_local', 'report_uuid');

        return $reports->map(function ($r) use ($lastByReport, $displayTz) {
            $id = (string)$r->id;
            return [
                'report_uuid' => $id,
                'name'        => $r->name ?? $id,
                'last_access' => $lastByReport[$id] ?? null,
                'created_at'  => $r->created_at ? ( ($r->created_at instanceof Carbon ? $r->created_at->copy() : Carbon::parse($r->created_at)) )->setTimezone($displayTz)->toDateTimeString() : null,
            ];
        })->values()->all();
    }

    /** PERFIL: KPIs y heatmap sobre renders; timeline con TODAS las acciones + report_name. */
    private function buildUserProfile(int $userId, Carbon $from, Carbon $to): array
    {
        $user = User::query()->find($userId);
        if (!$user) return [null, []];

        // Para KPIs y agregaciones, contamos SOLO 'render'
        $base = Audit::query()
            ->where('user_id', $userId)
            ->whereBetween('occurred_at', [$from, $to])
            ->where('action', 'render');

        $tz = addslashes(\App\Support\AppTimezone::current() ?? config('app.timezone'));
        $displayTz = \App\Support\AppTimezone::current() ?? config('app.timezone');

        $totalAccesos   = (clone $base)->count();
        $lastAccess     = (clone $base)->selectRaw("MAX(occurred_at AT TIME ZONE '$tz') AS la")->value('la');
        $reportesUnicos = (clone $base)->distinct('report_uuid')->count('report_uuid');
        $diasActivos    = (clone $base)->selectRaw("DATE(occurred_at AT TIME ZONE '$tz') AS d")->groupBy('d')->get()->count();

        $topReportsRows = (clone $base)
            ->select('report_uuid', DB::raw('COUNT(*) AS total'))
            ->groupBy('report_uuid')->orderByDesc('total')->limit(10)->get();

        $reportNames = Report::query()->whereIn('id', $topReportsRows->pluck('report_uuid'))->pluck('name','id');
        $topReports  = $topReportsRows->map(function ($r) use ($reportNames) {
            $id = (string)$r->report_uuid;
            return ['label' => $reportNames[$id] ?? $id, 'total' => (int)$r->total];
        })->values();

        // Heatmap por día/hora
        $gridRaw = (clone $base)
            ->selectRaw("EXTRACT(ISODOW FROM (occurred_at AT TIME ZONE '$tz'))::int AS dow, EXTRACT(HOUR FROM (occurred_at AT TIME ZONE '$tz'))::int AS hr, COUNT(*) AS total")
            ->groupBy('dow','hr')->get();

        $heatmap = [];
        for ($h = 0; $h < 24; $h++) {
            $row = ['hour' => sprintf('%02d:00', $h)];
            for ($d = 1; $d <= 7; $d++) $row[$d] = 0;
            $heatmap[$h] = $row;
        }
        foreach ($gridRaw as $r) $heatmap[$r->hr][$r->dow] = (int)$r->total;

        // Timeline: TODAS las acciones (hasta 500) + nombre de reporte
        $eventsRaw = Audit::query()
            ->where('user_id', $userId)
            ->whereBetween('occurred_at', [$from, $to])
            ->orderByDesc('occurred_at')->limit(500)
            ->get(['report_uuid']);

        $uuids = $eventsRaw->pluck('report_uuid')->filter()->unique()->values();
        $nameMap = $uuids->isEmpty()
            ? collect()
            : Report::query()->whereIn('id', $uuids)->pluck('name','id');

        $events = Audit::query()
            ->where('user_id', $userId)
            ->whereBetween('occurred_at', [$from, $to])
            ->orderByDesc('occurred_at')->limit(500)
            ->get(['occurred_at','action','report_uuid','page_name','bookmark','filters','duration_ms','success','error_message'])
            ->map(function ($e) use ($nameMap, $displayTz) {
                $rid = (string)$e->report_uuid;
                return [
                    'occurred_at' => ($e->occurred_at instanceof Carbon ? $e->occurred_at->copy() : Carbon::parse($e->occurred_at))->setTimezone($displayTz)->toDateTimeString(),
                    'action'      => (string)$e->action,
                    'report_uuid' => $rid,
                    'report_name' => $nameMap[$rid] ?? $rid,
                    'page_name'   => $e->page_name,
                    'bookmark'    => $e->bookmark,
                    'duration_ms' => $e->duration_ms,
                    'success'     => (bool)$e->success,
                    'error'       => $e->error_message,
                ];
            })->values()->all();

        $profile = [
            'user'           => ['id'=>$user->id, 'name'=>$user->name, 'email'=>$user->email],
            'from'           => $from->toDateString(),
            'to'             => $to->toDateString(),
            'kpis'           => [
                'totalAccesos'   => $totalAccesos,
                'ultimoAcceso'   => $lastAccess ?: null,
                'reportesUnicos' => $reportesUnicos,
                'diasActivos'    => $diasActivos,
            ],
            'topReports'     => $topReports,
            'heatmap'        => array_values($heatmap),
        ];

        return [$profile, $events];
    }

    /** Exporta la vista de analítica a un libro Excel con múltiples hojas (PhpSpreadsheet). */
    public function export(Request $request): StreamedResponse
    {
        // Evitar bytes extra
        if (class_exists(\Debugbar::class)) {
            \Debugbar::disable();
        }
        @ini_set('zlib.output_compression', 'Off');
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        $data = $this->composeViewData($request, true);

        $sheets = $this->buildExportSheets($data);
        if (empty($sheets)) {
            $sheets[] = [
                'name'    => 'Datos',
                'headers' => ['Mensaje'],
                'rows'    => [['No hay datos para exportar']],
            ];
        }

        // === PhpSpreadsheet ===
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Auditoría')
            ->setTitle('Auditoría de Métricas de Uso');

        // Escribir hojas
        foreach ($sheets as $idx => $sheet) {
            $name    = (string)($sheet['name'] ?? ('Hoja '.($idx+1)));
            $headers = (array)($sheet['headers'] ?? []);
            $rows    = (array)($sheet['rows'] ?? []);

            /** @var Worksheet $ws */
            if ($idx === 0) {
                $ws = $spreadsheet->getActiveSheet();
                $ws->setTitle(mb_substr($name, 0, 31));
            } else {
                $ws = new Worksheet($spreadsheet, mb_substr($name, 0, 31));
                $spreadsheet->addSheet($ws, $idx);
            }

            // Headers (fila 1)
            $col = 1;
            foreach ($headers as $h) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $coord     = $colLetter . '1';
                $ws->setCellValue($coord, (string)$h);
                $ws->getStyle($coord)->getFont()->setBold(true);
                $col++;
            }

            // Datos (desde fila 2)
            $r = 2;
            foreach ($rows as $row) {
                $c = 1;
                foreach ((array)$row as $val) {
                    $colLetter = Coordinate::stringFromColumnIndex($c);
                    $coord     = $colLetter . (string)$r;

                    if (is_int($val) || is_float($val)) {
                        $ws->setCellValueExplicit($coord, $val, DataType::TYPE_NUMERIC);
                    } elseif (is_bool($val)) {
                        $ws->setCellValueExplicit($coord, $val ? 'TRUE' : 'FALSE', DataType::TYPE_STRING);
                    } elseif ($val === null) {
                        $ws->setCellValueExplicit($coord, '', DataType::TYPE_STRING);
                    } else {
                        $ws->setCellValueExplicit($coord, (string)$val, DataType::TYPE_STRING);
                    }
                    $c++;
                }
                $r++;
            }

            // Auto-size columnas
            $maxCol = max(1, count($headers));
            for ($i = 1; $i <= $maxCol; $i++) {
                $colLetter = Coordinate::stringFromColumnIndex($i);
                $ws->getColumnDimension($colLetter)->setAutoSize(true);
            }
        } // ← cierre del foreach de hojas ✅

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'auditoria_' . now()->format('Ymd_His') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

        // Stream limpio
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'              => 'no-cache',
        ]);
    }

    /**
     * Prepara las hojas que se exportarán al libro de Excel.
     *
     * @param array<string, mixed> $data
     * @return array<int, array{name: string, headers: array<int, string>, rows: array<int, array<int, int|float|string|bool|null>>}>
     */
    private function buildExportSheets(array $data): array
    {
        $sheets = [];

        $appendSheet = static function (string $name, array $headers, array $rows) use (&$sheets): void {
            $normalisedRows = array_map(static fn (array $row): array => array_values($row), $rows);
            $sheets[] = [
                'name'    => $name,
                'headers' => array_values($headers),
                'rows'    => $normalisedRows,
            ];
        };

        $meta = $data['exportMeta'] ?? [];
        $tzLabel = '';
        if (isset($meta['displayTimezone'])) {
            $tz = $meta['displayTimezone'];
            if ($tz instanceof \DateTimeZone) {
                $tzLabel = $tz->getName();
            } elseif (is_string($tz)) {
                $tzLabel = $tz;
            }
        }

        $summaryRows = [
            ['Rango desde', $data['from'] ?? ''],
            ['Rango hasta', $data['to'] ?? ''],
        ];
        if ($tzLabel !== '') {
            $summaryRows[] = ['Zona horaria (visualizacion)', $tzLabel];
        }
        if (array_key_exists('totalAccesos', $data)) {
            $summaryRows[] = ['Total accesos', $data['totalAccesos']];
        }
        if (array_key_exists('totalUsuarios', $data)) {
            $summaryRows[] = ['Total usuarios', $data['totalUsuarios']];
        }
        if (array_key_exists('totalReportes', $data)) {
            $summaryRows[] = ['Total reportes', $data['totalReportes']];
        }
        if (!empty($meta['tab'])) {
            $summaryRows[] = ['Pestana solicitada', $meta['tab']];
        }
        $appendSheet('Resumen', ['Indicador', 'Valor'], $summaryRows);

        $monthsRows = [];
        foreach ($data['months'] ?? [] as $row) {
            if (!is_array($row)) continue;
            $monthsRows[] = [$row['label'] ?? '', $row['total'] ?? 0];
        }
        if (!empty($monthsRows)) {
            $appendSheet('Dashboard - Mensual', ['Mes', 'Accesos'], $monthsRows);
        }

        $dailyRows = [];
        foreach ($data['daily'] ?? [] as $row) {
            if (!is_array($row)) continue;
            $dailyRows[] = [$row['date'] ?? '', $row['total'] ?? 0];
        }
        if (!empty($dailyRows)) {
            $appendSheet('Dashboard - Diario', ['Fecha', 'Accesos'], $dailyRows);
        }

        $heatmapRows = [];
        foreach ($data['heatmap'] ?? [] as $row) {
            if (!is_array($row)) continue;
            $heatmapRows[] = [
                $row['hour'] ?? '',
                $row[1] ?? 0, $row[2] ?? 0, $row[3] ?? 0, $row[4] ?? 0,
                $row[5] ?? 0, $row[6] ?? 0, $row[7] ?? 0,
            ];
        }
        if (!empty($heatmapRows)) {
            $appendSheet(
                'Dashboard - Heatmap',
                ['Hora', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'],
                $heatmapRows
            );
        }

        $topUsersRows = [];
        foreach (collect($data['topUsers'] ?? []) as $row) {
            if (!is_array($row)) continue;
            $topUsersRows[] = [$row['name'] ?? '', $row['total'] ?? 0];
        }
        if (!empty($topUsersRows)) {
            $appendSheet('Dashboard - Top usuarios', ['Usuario', 'Accesos'], $topUsersRows);
        }

        $topReportsRows = [];
        foreach (collect($data['topReports'] ?? []) as $row) {
            if (!is_array($row)) continue;
            $topReportsRows[] = [$row['label'] ?? '', $row['total'] ?? 0];
        }
        if (!empty($topReportsRows)) {
            $appendSheet('Dashboard - Top reportes', ['Reporte', 'Accesos'], $topReportsRows);
        }

        $topErrorRows = [];
        foreach (collect($data['topErrorReports'] ?? []) as $row) {
            if (!is_array($row)) continue;
            $topErrorRows[] = [$row['label'] ?? '', $row['total'] ?? 0];
        }
        if (!empty($topErrorRows)) {
            $appendSheet('Dashboard - Errores', ['Reporte', 'Errores'], $topErrorRows);
        }

        $alertsRows = [];
        foreach ($data['alerts'] ?? [] as $row) {
            if (!is_array($row)) continue;
            $alertsRows[] = [$row['type'] ?? '', $row['msg'] ?? ''];
        }
        if (!empty($alertsRows)) {
            $appendSheet('Dashboard - Alertas', ['Tipo', 'Mensaje'], $alertsRows);
        }

        $userSheetHeaders = ['ID usuario', 'Nombre', 'Email', 'Accesos', 'Ultimo acceso', 'Creado'];
        $usersMostRows = [];
        foreach ($data['usersMost'] ?? [] as $row) {
            if (!is_array($row)) continue;
            $usersMostRows[] = [
                $row['user_id'] ?? null,
                $row['name'] ?? '',
                $row['email'] ?? '',
                $row['total'] ?? 0,
                $row['last_access'] ?? '',
                $row['created_at'] ?? '',
            ];
        }
        $appendSheet('Usuarios - Mas activos', $userSheetHeaders, $usersMostRows);

        $usersLeastRows = [];
        foreach ($data['usersLeast'] ?? [] as $row) {
            if (!is_array($row)) continue;
            $usersLeastRows[] = [
                $row['user_id'] ?? null,
                $row['name'] ?? '',
                $row['email'] ?? '',
                $row['total'] ?? 0,
                $row['last_access'] ?? '',
                $row['created_at'] ?? '',
            ];
        }
        $appendSheet('Usuarios - Menos activos', $userSheetHeaders, $usersLeastRows);

        $usersInactiveHeaders = ['ID usuario', 'Nombre', 'Email', 'Ultimo acceso', 'Creado'];
        $usersInactiveRows = [];
        foreach ($data['usersNoAct'] ?? [] as $row) {
            if (!is_array($row)) continue;
            $usersInactiveRows[] = [
                $row['user_id'] ?? null,
                $row['name'] ?? '',
                $row['email'] ?? '',
                $row['last_access'] ?? '',
                $row['created_at'] ?? '',
            ];
        }
        $appendSheet('Usuarios - Sin accesos', $usersInactiveHeaders, $usersInactiveRows);

        $reportHeaders = ['ID reporte', 'Nombre', 'Accesos', 'Ultimo acceso'];
        $reportsMostRows = [];
        foreach ($data['reportsMost'] ?? [] as $row) {
            if (!is_array($row)) continue;
            $reportsMostRows[] = [
                $row['report_uuid'] ?? '',
                $row['name'] ?? '',
                $row['total'] ?? 0,
                $row['last_access'] ?? '',
            ];
        }
        $appendSheet('Reportes - Mas accesados', $reportHeaders, $reportsMostRows);

        $reportsLeastRows = [];
        foreach ($data['reportsLeast'] ?? [] as $row) {
            if (!is_array($row)) continue;
            $reportsLeastRows[] = [
                $row['report_uuid'] ?? '',
                $row['name'] ?? '',
                $row['total'] ?? 0,
                $row['last_access'] ?? '',
            ];
        }
        $appendSheet('Reportes - Menos accesados', $reportHeaders, $reportsLeastRows);

        $reportsInactiveHeaders = ['ID reporte', 'Nombre', 'Ultimo acceso', 'Creado'];
        $reportsInactiveRows = [];
        foreach ($data['reportsNoAcc'] ?? [] as $row) {
            if (!is_array($row)) continue;
            $reportsInactiveRows[] = [
                $row['report_uuid'] ?? '',
                $row['name'] ?? '',
                $row['last_access'] ?? '',
                $row['created_at'] ?? '',
            ];
        }
        $appendSheet('Reportes - Sin accesos', $reportsInactiveHeaders, $reportsInactiveRows);

        $userProfile = $data['userProfile'] ?? null;
        if (is_array($userProfile)) {
            $profileRows = [];
            $userInfo = $userProfile['user'] ?? [];
            $profileRows[] = ['Usuario ID', $userInfo['id'] ?? ''];
            $profileRows[] = ['Nombre', $userInfo['name'] ?? ''];
            $profileRows[] = ['Email', $userInfo['email'] ?? ''];
            $profileRows[] = ['Rango desde', $userProfile['from'] ?? ''];
            $profileRows[] = ['Rango hasta', $userProfile['to'] ?? ''];

            $kpis = $userProfile['kpis'] ?? [];
            if (is_array($kpis)) {
                foreach ($kpis as $label => $value) {
                    $profileRows[] = [ucwords(str_replace(['_', '-'], ' ', (string) $label)), $value];
                }
            }
            $appendSheet('Perfil - Resumen', ['Clave', 'Valor'], $profileRows);

            $profileHeatmapRows = [];
            foreach ($userProfile['heatmap'] ?? [] as $row) {
                if (!is_array($row)) continue;
                $profileHeatmapRows[] = [
                    $row['hour'] ?? '',
                    $row[1] ?? 0, $row[2] ?? 0, $row[3] ?? 0, $row[4] ?? 0,
                    $row[5] ?? 0, $row[6] ?? 0, $row[7] ?? 0,
                ];
            }
            if (!empty($profileHeatmapRows)) {
                $appendSheet('Perfil - Heatmap', ['Hora', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'], $profileHeatmapRows);
            }

            $profileTopReportsRows = [];
            foreach (collect($userProfile['topReports'] ?? []) as $row) {
                if (!is_array($row)) continue;
                $profileTopReportsRows[] = [$row['label'] ?? '', $row['total'] ?? 0];
            }
            if (!empty($profileTopReportsRows)) {
                $appendSheet('Perfil - Top reportes', ['Reporte', 'Accesos'], $profileTopReportsRows);
            }
        }

        $userEvents = $data['userEvents'] ?? [];
        if (!empty($userEvents)) {
            $eventRows = [];
            foreach ($userEvents as $row) {
                if (!is_array($row)) continue;
                $eventRows[] = [
                    $row['occurred_at'] ?? '',
                    $row['action'] ?? '',
                    $row['report_uuid'] ?? '',
                    $row['report_name'] ?? '',
                    $row['page_name'] ?? '',
                    $row['bookmark'] ?? '',
                    $row['duration_ms'] ?? null,
                    isset($row['success']) ? ($row['success'] ? 'Si' : 'No') : '',
                    $row['error'] ?? '',
                ];
            }
            $appendSheet('Perfil - Eventos', ['Fecha', 'Accion', 'Reporte ID', 'Reporte', 'Pagina', 'Bookmark', 'Duracion (ms)', 'Exito', 'Error'], $eventRows);
        }

        return $sheets;
    }
}
