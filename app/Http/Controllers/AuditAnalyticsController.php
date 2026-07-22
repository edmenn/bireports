<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Report;
use App\Models\User;
use App\Support\AppTimezone;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
// PhpSpreadsheet (full Excel library)
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AuditAnalyticsController extends Controller
{
    /** ONLY action that counts as an "access" event. Adjust to match your dataset. */
    private const ACCESS_ACTIONS = ['render', 'view'];

    public function index(Request $request): View
    {
        $data = $this->composeViewData($request);

        return view('audit.index', $data);
    }

    /**
     * Build analytics payload reused by both the HTML view and the export.
     */
    private function composeViewData(Request $request, bool $loadAllTabs = false): array
    {
        $tab = (string) $request->input('tab', 'dashboard');

        $appTz = AppTimezone::current() ?? config('app.timezone');

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
            'totalAccesos' => (clone $baseScoped)->count(),
            'totalUsuarios' => (clone $baseScoped)->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            'totalReportes' => (clone $baseScoped)->distinct('report_uuid')->count('report_uuid'),
        ];

        $dashboard = $this->buildDashboard($baseGlobal, $from, $to);

        $usersMost = [];
        $usersLeast = [];
        $usersNoAct = [];

        $reportsMost = [];
        $reportsLeast = [];
        $reportsNoAcc = [];

        $userProfile = null;
        $userEvents = [];

        $tabsToHydrate = $loadAllTabs
            ? ['users-most', 'users-least', 'users-inactive', 'reports-most', 'reports-least', 'reports-inactive', 'user-profile']
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

        $dowLabels = [
            1 => __('common.days.monday'),
            2 => __('common.days.tuesday'),
            3 => __('common.days.wednesday'),
            4 => __('common.days.thursday'),
            5 => __('common.days.friday'),
            6 => __('common.days.saturday'),
            7 => __('common.days.sunday'),
        ];

        return array_merge([
            'tab' => $tab,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'userId' => $userId,
            'usersList' => $usersList,
            'dowLabels' => $dowLabels,
            'accessAction' => 'render+view',
            'exportMeta' => [
                'fromUtc' => clone $from,
                'toUtc' => clone $to,
                'displayTimezone' => $appTz,
                'userId' => $userId,
                'tab' => $tab,
            ],
        ], $totals, $dashboard, [
            'usersMost' => $usersMost,
            'usersLeast' => $usersLeast,
            'usersNoAct' => $usersNoAct,
            'reportsMost' => $reportsMost,
            'reportsLeast' => $reportsLeast,
            'reportsNoAcc' => $reportsNoAcc,
            'userProfile' => $userProfile,
            'userEvents' => $userEvents,
        ]);
    }

    /** Dashboard global (renders). */
    private function buildDashboard($base, Carbon $from, Carbon $to): array
    {
        $tz = AppTimezone::current();
        $displayTz = AppTimezone::current() ?? config('app.timezone');

        $monthsRaw = (clone $base)
            ->selectRaw("to_char(date_trunc('month', occurred_at AT TIME ZONE ?), 'YYYY-MM') AS ym, COUNT(*) AS total", [$tz])
            ->groupBy('ym')->orderBy('ym')->get()->keyBy('ym');

        $months = [];
        $cursor = (clone $from)->startOfMonth();
        $end = (clone $to)->startOfMonth();
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m');
            $months[] = [
                'label' => $cursor->translatedFormat('F'),
                'total' => isset($monthsRaw[$key]) ? (int) $monthsRaw[$key]->total : 0,
            ];
            $cursor->addMonth();
        }

        $gridRaw = (clone $base)
            ->selectRaw('EXTRACT(ISODOW FROM (occurred_at AT TIME ZONE ?))::int AS dow, EXTRACT(HOUR FROM (occurred_at AT TIME ZONE ?))::int AS hr, COUNT(*) AS total', [$tz, $tz])
            ->groupBy('dow', 'hr')->get();

        $heatmap = [];
        for ($h = 0; $h < 24; $h++) {
            $row = ['hour' => sprintf('%02d:00', $h)];
            for ($d = 1; $d <= 7; $d++) {
                $row[$d] = 0;
            }
            $heatmap[$h] = $row;
        }
        foreach ($gridRaw as $r) {
            $heatmap[$r->hr][$r->dow] = (int) $r->total;
        }

        $dailyRaw = (clone $base)
            ->selectRaw('DATE(occurred_at AT TIME ZONE ?) AS d, COUNT(*) AS total', [$tz])
            ->groupBy('d')->orderBy('d')->get()->keyBy('d');

        $daily = [];
        $cur = (clone $from)->startOfDay();
        while ($cur->lte($to)) {
            $k = $cur->toDateString();
            $daily[] = ['date' => $k, 'total' => isset($dailyRaw[$k]) ? (int) $dailyRaw[$k]->total : 0];
            $cur->addDay();
        }

        $topUsers = (clone $base)
            ->select('user_id', DB::raw('COUNT(*) AS total'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')->orderByDesc('total')->limit(10)->get()
            ->map(function ($r) {
                $u = User::query()->find($r->user_id);

                return ['name' => $u?->name ?? __('audit.placeholders.user', ['id' => $r->user_id]), 'total' => (int) $r->total];
            })->values();

        $topReportsRows = (clone $base)
            ->select('report_uuid', DB::raw('COUNT(*) AS total'))
            ->groupBy('report_uuid')->orderByDesc('total')->limit(10)->get();

        $reportNames = Report::query()->whereIn('id', $topReportsRows->pluck('report_uuid'))->pluck('name', 'id');
        $topReports = $topReportsRows->map(function ($r) use ($reportNames) {
            $id = (string) $r->report_uuid;

            return ['label' => $reportNames[$id] ?? $id, 'total' => (int) $r->total];
        })->values();

        $since7 = (clone $to)->copy()->subDays(6)->startOfDay();
        $since30 = (clone $to)->copy()->subDays(29)->startOfDay();

        $activeUsers7 = (clone $base)->where('occurred_at', '>=', $since7)->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $activeUsers30 = (clone $base)->where('occurred_at', '>=', $since30)->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $activeReports7 = (clone $base)->where('occurred_at', '>=', $since7)->distinct('report_uuid')->count('report_uuid');
        $activeReports30 = (clone $base)->where('occurred_at', '>=', $since30)->distinct('report_uuid')->count('report_uuid');

        $errors7 = Audit::query()->whereBetween('occurred_at', [$since7, $to])->where('action', 'error')->count();
        $views7 = (clone $base)->where('occurred_at', '>=', $since7)->count();
        $errorRate7 = $views7 > 0 ? round(($errors7 / $views7) * 100, 1) : 0.0;

        $topErrorReportsRows = Audit::query()
            ->whereBetween('occurred_at', [$since30, $to])
            ->where('action', 'error')
            ->select('report_uuid', DB::raw('COUNT(*) AS total'))
            ->groupBy('report_uuid')->orderByDesc('total')->limit(10)->get();

        $errReportNames = Report::query()->whereIn('id', $topErrorReportsRows->pluck('report_uuid'))->pluck('name', 'id');
        $topErrorReports = $topErrorReportsRows->map(function ($r) use ($errReportNames) {
            $id = (string) $r->report_uuid;

            return ['label' => $errReportNames[$id] ?? $id, 'total' => (int) $r->total];
        })->values();

        $since7_from = $since7;
        $prev7_from = (clone $since7_from)->subDays(7);
        $prev7_to = (clone $since7_from)->subDay()->endOfDay();

        $cnt7 = (clone $base)->whereBetween('occurred_at', [$since7, $to])->count();
        $cntp7 = (clone $base)->whereBetween('occurred_at', [$prev7_from, $prev7_to])->count();
        $delta7 = $cntp7 > 0 ? round((($cnt7 - $cntp7) / $cntp7) * 100, 1) : null;

        $alerts = [];
        if ($delta7 !== null && $delta7 <= -30.0) {
            $alerts[] = [
                'type' => 'warning',
                'msg' => __('audit.alerts.usage_drop', [
                    'current' => $cnt7,
                    'previous' => $cntp7,
                    'delta' => $delta7,
                ]),
            ];
        }
        if ($errorRate7 >= 5.0 && $errors7 >= 5) {
            $alerts[] = [
                'type' => 'danger',
                'msg' => __('audit.alerts.high_error_rate', [
                    'rate' => $errorRate7,
                    'errors' => $errors7,
                    'views' => $views7,
                ]),
            ];
        }

        return [
            'months' => $months,
            'topUsers' => $topUsers,
            'topReports' => $topReports,
            'heatmap' => array_values($heatmap),
            'daily' => $daily,
            'activeUsers7' => $activeUsers7,
            'activeUsers30' => $activeUsers30,
            'activeReports7' => $activeReports7,
            'activeReports30' => $activeReports30,
            'errors7' => $errors7,
            'errorRate7' => $errorRate7,
            'topErrorReports' => $topErrorReports,
            'delta7' => $delta7,
            'alerts' => $alerts,
        ];
    }

    /** Rankings Usuarios (renders). */
    private function usersRank($base, string $direction, int $limit): array
    {
        $displayTz = AppTimezone::current() ?? config('app.timezone');
        $tz = AppTimezone::current();
        $rows = (clone $base)
            ->select('user_id')
            ->selectRaw('COUNT(*) AS total, MAX(occurred_at AT TIME ZONE ?) AS last_access_local', [$tz])
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderBy('total', $direction === 'asc' ? 'asc' : 'desc')
            ->limit($limit)->get();

        return $rows->map(function ($r) use ($displayTz) {
            $u = User::query()->find($r->user_id);

            return [
                'user_id' => (int) $r->user_id,
                'name' => $u?->name ?? __('audit.placeholders.user', ['id' => $r->user_id]),
                'email' => $u?->email,
                'total' => (int) $r->total,
                'last_access' => $r->last_access_local ?? null,
                'created_at' => $u?->created_at ? (($u->created_at instanceof Carbon ? $u->created_at->copy() : Carbon::parse($u->created_at)))->setTimezone($displayTz)->toDateTimeString() : null,
            ];
        })->values()->all();
    }

    /** Usuarios sin renders en rango. */
    private function usersWithoutAccess(Carbon $from, Carbon $to): array
    {
        $displayTz = AppTimezone::current() ?? config('app.timezone');
        $tz = AppTimezone::current();
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
            ->select('user_id')
            ->selectRaw('MAX(occurred_at AT TIME ZONE ?) AS last_access_local', [$tz])
            ->whereNotNull('user_id')
            ->where('action', 'render')
            ->groupBy('user_id')->pluck('last_access_local', 'user_id');

        return $users->map(function ($u) use ($lastByUser, $displayTz) {
            return [
                'user_id' => (int) $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'last_access' => $lastByUser[$u->id] ?? null,
                'created_at' => $u->created_at ? (($u->created_at instanceof Carbon ? $u->created_at->copy() : Carbon::parse($u->created_at)))->setTimezone($displayTz)->toDateTimeString() : null,
            ];
        })->values()->all();
    }

    /** Rankings Reportes (renders). */
    private function reportsRank($base, string $direction, int $limit): array
    {
        $displayTz = AppTimezone::current() ?? config('app.timezone');
        $tz = AppTimezone::current();
        $rows = (clone $base)
            ->select('report_uuid')
            ->selectRaw('COUNT(*) AS total, MAX(occurred_at AT TIME ZONE ?) AS last_access_local', [$tz])
            ->groupBy('report_uuid')
            ->orderBy('total', $direction === 'asc' ? 'asc' : 'desc')
            ->limit($limit)->get();

        $names = Report::query()->whereIn('id', $rows->pluck('report_uuid'))->pluck('name', 'id');

        return $rows->map(function ($r) use ($names) {
            $id = (string) $r->report_uuid;

            return [
                'report_uuid' => $id,
                'name' => $names[$id] ?? $id,
                'total' => (int) $r->total,
                'last_access' => $r->last_access_local ?? null,
            ];
        })->values()->all();
    }

    /** Reportes sin renders en rango. */
    private function reportsWithoutAccess(Carbon $from, Carbon $to): array
    {
        $displayTz = AppTimezone::current() ?? config('app.timezone');
        $tz = AppTimezone::current();
        $sub = Audit::query()
            ->select('report_uuid')
            ->whereBetween('occurred_at', [$from, $to])
            ->where('action', 'render')
            ->groupBy('report_uuid');

        $reports = Report::query()
            ->whereNotIn('id', $sub)
            ->orderBy('created_at', 'desc')
            ->limit(500)->get(['id', 'name', 'created_at']);

        $lastByReport = Audit::query()
            ->select('report_uuid')
            ->selectRaw('MAX(occurred_at AT TIME ZONE ?) AS last_access_local', [$tz])
            ->where('action', 'render')
            ->groupBy('report_uuid')->pluck('last_access_local', 'report_uuid');

        return $reports->map(function ($r) use ($lastByReport, $displayTz) {
            $id = (string) $r->id;

            return [
                'report_uuid' => $id,
                'name' => $r->name ?? $id,
                'last_access' => $lastByReport[$id] ?? null,
                'created_at' => $r->created_at ? (($r->created_at instanceof Carbon ? $r->created_at->copy() : Carbon::parse($r->created_at)))->setTimezone($displayTz)->toDateTimeString() : null,
            ];
        })->values()->all();
    }

    /** PERFIL: KPIs y heatmap sobre renders; timeline con TODAS las acciones + report_name. */
    private function buildUserProfile(int $userId, Carbon $from, Carbon $to): array
    {
        $user = User::query()->find($userId);
        if (! $user) {
            return [null, []];
        }

        // Para KPIs y agregaciones, contamos SOLO 'render'
        $base = Audit::query()
            ->where('user_id', $userId)
            ->whereBetween('occurred_at', [$from, $to])
            ->where('action', 'render');

        $tz = AppTimezone::current();
        $displayTz = AppTimezone::current() ?? config('app.timezone');

        $totalAccesos = (clone $base)->count();
        $lastAccess = (clone $base)->selectRaw('MAX(occurred_at AT TIME ZONE ?) AS la', [$tz])->value('la');
        $reportesUnicos = (clone $base)->distinct('report_uuid')->count('report_uuid');
        $diasActivos = (clone $base)->selectRaw('DATE(occurred_at AT TIME ZONE ?) AS d', [$tz])->groupBy('d')->get()->count();

        $topReportsRows = (clone $base)
            ->select('report_uuid', DB::raw('COUNT(*) AS total'))
            ->groupBy('report_uuid')->orderByDesc('total')->limit(10)->get();

        $reportNames = Report::query()->whereIn('id', $topReportsRows->pluck('report_uuid'))->pluck('name', 'id');
        $topReports = $topReportsRows->map(function ($r) use ($reportNames) {
            $id = (string) $r->report_uuid;

            return ['label' => $reportNames[$id] ?? $id, 'total' => (int) $r->total];
        })->values();

        // Heatmap grouped by day and hour
        $gridRaw = (clone $base)
            ->selectRaw('EXTRACT(ISODOW FROM (occurred_at AT TIME ZONE ?))::int AS dow, EXTRACT(HOUR FROM (occurred_at AT TIME ZONE ?))::int AS hr, COUNT(*) AS total', [$tz, $tz])
            ->groupBy('dow', 'hr')->get();

        $heatmap = [];
        for ($h = 0; $h < 24; $h++) {
            $row = ['hour' => sprintf('%02d:00', $h)];
            for ($d = 1; $d <= 7; $d++) {
                $row[$d] = 0;
            }
            $heatmap[$h] = $row;
        }
        foreach ($gridRaw as $r) {
            $heatmap[$r->hr][$r->dow] = (int) $r->total;
        }

        // Timeline: TODAS las acciones (hasta 500) + nombre de reporte
        $eventsRaw = Audit::query()
            ->where('user_id', $userId)
            ->whereBetween('occurred_at', [$from, $to])
            ->orderByDesc('occurred_at')->limit(500)
            ->get(['report_uuid']);

        $uuids = $eventsRaw->pluck('report_uuid')->filter()->unique()->values();
        $nameMap = $uuids->isEmpty()
            ? collect()
            : Report::query()->whereIn('id', $uuids)->pluck('name', 'id');

        $events = Audit::query()
            ->where('user_id', $userId)
            ->whereBetween('occurred_at', [$from, $to])
            ->orderByDesc('occurred_at')->limit(500)
            ->get(['occurred_at', 'action', 'report_uuid', 'page_name', 'bookmark', 'filters', 'duration_ms', 'success', 'error_message'])
            ->map(function ($e) use ($nameMap, $displayTz) {
                $rid = (string) $e->report_uuid;

                return [
                    'occurred_at' => ($e->occurred_at instanceof Carbon ? $e->occurred_at->copy() : Carbon::parse($e->occurred_at))->setTimezone($displayTz)->toDateTimeString(),
                    'action' => (string) $e->action,
                    'report_uuid' => $rid,
                    'report_name' => $nameMap[$rid] ?? $rid,
                    'page_name' => $e->page_name,
                    'bookmark' => $e->bookmark,
                    'duration_ms' => $e->duration_ms,
                    'success' => (bool) $e->success,
                    'error' => $e->error_message,
                ];
            })->values()->all();

        $profile = [
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'kpis' => [
                'totalAccesos' => $totalAccesos,
                'ultimoAcceso' => $lastAccess ?: null,
                'reportesUnicos' => $reportesUnicos,
                'diasActivos' => $diasActivos,
            ],
            'topReports' => $topReports,
            'heatmap' => array_values($heatmap),
        ];

        return [$profile, $events];
    }

    /** Export the analytics view to a multi-sheet Excel workbook (PhpSpreadsheet). */
    public function export(Request $request): StreamedResponse
    {
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
                'name' => __('audit.export.no_data_sheet'),
                'headers' => [__('audit.export.headers.message')],
                'rows' => [[__('audit.export.no_data_message')]],
            ];
        }

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator(__('audit.export.creator'))
            ->setTitle(__('audit.export.title'));

        foreach ($sheets as $idx => $sheet) {
            $name = (string) ($sheet['name'] ?? __('audit.export.default_sheet', ['number' => $idx + 1]));
            $headers = (array) ($sheet['headers'] ?? []);
            $rows = (array) ($sheet['rows'] ?? []);

            /** @var Worksheet $ws */
            if ($idx === 0) {
                $ws = $spreadsheet->getActiveSheet();
                $ws->setTitle(mb_substr($name, 0, 31));
            } else {
                $ws = new Worksheet($spreadsheet, mb_substr($name, 0, 31));
                $spreadsheet->addSheet($ws, $idx);
            }

            $col = 1;
            foreach ($headers as $h) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $coord = $colLetter.'1';
                $ws->setCellValue($coord, (string) $h);
                $ws->getStyle($coord)->getFont()->setBold(true);
                $col++;
            }

            $r = 2;
            foreach ($rows as $row) {
                $c = 1;
                foreach ((array) $row as $val) {
                    $colLetter = Coordinate::stringFromColumnIndex($c);
                    $coord = $colLetter.(string) $r;

                    if (is_int($val) || is_float($val)) {
                        $ws->setCellValueExplicit($coord, $val, DataType::TYPE_NUMERIC);
                    } elseif (is_bool($val)) {
                        $ws->setCellValueExplicit($coord, $val ? 'TRUE' : 'FALSE', DataType::TYPE_STRING);
                    } elseif ($val === null) {
                        $ws->setCellValueExplicit($coord, '', DataType::TYPE_STRING);
                    } else {
                        $ws->setCellValueExplicit($coord, (string) $val, DataType::TYPE_STRING);
                    }
                    $c++;
                }
                $r++;
            }

            $maxCol = max(1, count($headers));
            for ($i = 1; $i <= $maxCol; $i++) {
                $colLetter = Coordinate::stringFromColumnIndex($i);
                $ws->getColumnDimension($colLetter)->setAutoSize(true);
            }
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = __('audit.export.filename_prefix').now()->format('Ymd_His').'.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Prepare the sheets that will be exported to the Excel workbook.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, array{name: string, headers: array<int, string>, rows: array<int, array<int, int|float|string|bool|null>>}>
     */
    private function buildExportSheets(array $data): array
    {
        $sheets = [];

        $appendSheet = static function (string $name, array $headers, array $rows) use (&$sheets): void {
            $normalisedRows = array_map(static fn (array $row): array => array_values($row), $rows);
            $sheets[] = [
                'name' => $name,
                'headers' => array_values($headers),
                'rows' => $normalisedRows,
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

        $sheetNames = [
            'summary' => __('audit.export.sheets.summary'),
            'dashboard_monthly' => __('audit.export.sheets.dashboard_monthly'),
            'dashboard_daily' => __('audit.export.sheets.dashboard_daily'),
            'dashboard_heatmap' => __('audit.export.sheets.dashboard_heatmap'),
            'dashboard_top_users' => __('audit.export.sheets.dashboard_top_users'),
            'dashboard_top_reports' => __('audit.export.sheets.dashboard_top_reports'),
            'dashboard_errors' => __('audit.export.sheets.dashboard_errors'),
            'dashboard_alerts' => __('audit.export.sheets.dashboard_alerts'),
            'users_most' => __('audit.export.sheets.users_most'),
            'users_least' => __('audit.export.sheets.users_least'),
            'users_inactive' => __('audit.export.sheets.users_inactive'),
            'reports_most' => __('audit.export.sheets.reports_most'),
            'reports_least' => __('audit.export.sheets.reports_least'),
            'reports_inactive' => __('audit.export.sheets.reports_inactive'),
            'profile_summary' => __('audit.export.sheets.profile_summary'),
            'profile_heatmap' => __('audit.export.sheets.profile_heatmap'),
            'profile_top_reports' => __('audit.export.sheets.profile_top_reports'),
            'profile_events' => __('audit.export.sheets.profile_events'),
        ];

        $headers = [
            'message' => __('audit.export.headers.message'),
            'indicator' => __('audit.export.headers.indicator'),
            'value' => __('audit.export.headers.value'),
            'month' => __('audit.export.headers.month'),
            'date' => __('audit.export.headers.date'),
            'hour' => __('audit.export.headers.hour'),
            'user' => __('audit.export.headers.user'),
            'report' => __('audit.export.headers.report'),
            'errors' => __('audit.export.headers.errors'),
            'type' => __('audit.export.headers.type'),
            'user_id' => __('audit.export.headers.user_id'),
            'name' => __('audit.export.headers.name'),
            'email' => __('audit.export.headers.email'),
            'accesses' => __('audit.export.headers.accesses'),
            'last_access' => __('audit.export.headers.last_access'),
            'created' => __('audit.export.headers.created'),
            'report_id' => __('audit.export.headers.report_id'),
            'bookmark' => __('audit.export.headers.bookmark'),
            'duration_ms' => __('audit.export.headers.duration_ms'),
            'success' => __('audit.export.headers.success'),
            'error' => __('audit.export.headers.error'),
            'key' => __('audit.export.headers.key'),
            'action' => __('audit.export.headers.action'),
            'page' => __('audit.export.headers.page'),
        ];

        $summaryLabels = [
            'range_from' => __('audit.summary.range_from'),
            'range_to' => __('audit.summary.range_to'),
            'display_timezone' => __('audit.summary.display_timezone'),
            'total_accesses' => __('audit.summary.total_accesses'),
            'total_users' => __('audit.summary.total_users'),
            'total_reports' => __('audit.summary.total_reports'),
            'requested_tab' => __('audit.summary.requested_tab'),
        ];

        $weekdays = [
            __('common.days.monday'),
            __('common.days.tuesday'),
            __('common.days.wednesday'),
            __('common.days.thursday'),
            __('common.days.friday'),
            __('common.days.saturday'),
            __('common.days.sunday'),
        ];

        $summaryRows = [
            [$summaryLabels['range_from'], $data['from'] ?? ''],
            [$summaryLabels['range_to'], $data['to'] ?? ''],
        ];
        if ($tzLabel !== '') {
            $summaryRows[] = [$summaryLabels['display_timezone'], $tzLabel];
        }
        if (array_key_exists('totalAccesos', $data)) {
            $summaryRows[] = [$summaryLabels['total_accesses'], $data['totalAccesos']];
        }
        if (array_key_exists('totalUsuarios', $data)) {
            $summaryRows[] = [$summaryLabels['total_users'], $data['totalUsuarios']];
        }
        if (array_key_exists('totalReportes', $data)) {
            $summaryRows[] = [$summaryLabels['total_reports'], $data['totalReportes']];
        }
        if (! empty($meta['tab'])) {
            $summaryRows[] = [$summaryLabels['requested_tab'], $meta['tab']];
        }
        $appendSheet($sheetNames['summary'], [$headers['indicator'], $headers['value']], $summaryRows);

        $monthsRows = [];
        foreach ($data['months'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $monthsRows[] = [$row['label'] ?? '', $row['total'] ?? 0];
        }
        if (! empty($monthsRows)) {
            $appendSheet($sheetNames['dashboard_monthly'], [$headers['month'], $headers['accesses']], $monthsRows);
        }

        $dailyRows = [];
        foreach ($data['daily'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $dailyRows[] = [$row['date'] ?? '', $row['total'] ?? 0];
        }
        if (! empty($dailyRows)) {
            $appendSheet($sheetNames['dashboard_daily'], [$headers['date'], $headers['accesses']], $dailyRows);
        }

        $heatmapRows = [];
        foreach ($data['heatmap'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $heatmapRows[] = [
                $row['hour'] ?? '',
                $row[1] ?? 0,
                $row[2] ?? 0,
                $row[3] ?? 0,
                $row[4] ?? 0,
                $row[5] ?? 0,
                $row[6] ?? 0,
                $row[7] ?? 0,
            ];
        }
        if (! empty($heatmapRows)) {
            $appendSheet(
                $sheetNames['dashboard_heatmap'],
                array_merge([$headers['hour']], $weekdays),
                $heatmapRows
            );
        }

        $topUsersRows = [];
        foreach (collect($data['topUsers'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $topUsersRows[] = [$row['name'] ?? '', $row['total'] ?? 0];
        }
        if (! empty($topUsersRows)) {
            $appendSheet($sheetNames['dashboard_top_users'], [$headers['user'], $headers['accesses']], $topUsersRows);
        }

        $topReportsRows = [];
        foreach (collect($data['topReports'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $topReportsRows[] = [$row['label'] ?? '', $row['total'] ?? 0];
        }
        if (! empty($topReportsRows)) {
            $appendSheet($sheetNames['dashboard_top_reports'], [$headers['report'], $headers['accesses']], $topReportsRows);
        }

        $topErrorRows = [];
        foreach (collect($data['topErrorReports'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $topErrorRows[] = [$row['label'] ?? '', $row['total'] ?? 0];
        }
        if (! empty($topErrorRows)) {
            $appendSheet($sheetNames['dashboard_errors'], [$headers['report'], $headers['errors']], $topErrorRows);
        }

        $alertsRows = [];
        foreach ($data['alerts'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $alertsRows[] = [$row['type'] ?? '', $row['msg'] ?? ''];
        }
        if (! empty($alertsRows)) {
            $appendSheet($sheetNames['dashboard_alerts'], [$headers['type'], $headers['message']], $alertsRows);
        }

        $userSheetHeaders = [
            $headers['user_id'],
            $headers['name'],
            $headers['email'],
            $headers['accesses'],
            $headers['last_access'],
            $headers['created'],
        ];
        $usersMostRows = [];
        foreach ($data['usersMost'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $usersMostRows[] = [
                $row['user_id'] ?? null,
                $row['name'] ?? '',
                $row['email'] ?? '',
                $row['total'] ?? 0,
                $row['last_access'] ?? '',
                $row['created_at'] ?? '',
            ];
        }
        $appendSheet($sheetNames['users_most'], $userSheetHeaders, $usersMostRows);

        $usersLeastRows = [];
        foreach ($data['usersLeast'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $usersLeastRows[] = [
                $row['user_id'] ?? null,
                $row['name'] ?? '',
                $row['email'] ?? '',
                $row['total'] ?? 0,
                $row['last_access'] ?? '',
                $row['created_at'] ?? '',
            ];
        }
        $appendSheet($sheetNames['users_least'], $userSheetHeaders, $usersLeastRows);

        $usersInactiveHeaders = [
            $headers['user_id'],
            $headers['name'],
            $headers['email'],
            $headers['last_access'],
            $headers['created'],
        ];
        $usersInactiveRows = [];
        foreach ($data['usersNoAct'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $usersInactiveRows[] = [
                $row['user_id'] ?? null,
                $row['name'] ?? '',
                $row['email'] ?? '',
                $row['last_access'] ?? '',
                $row['created_at'] ?? '',
            ];
        }
        $appendSheet($sheetNames['users_inactive'], $usersInactiveHeaders, $usersInactiveRows);

        $reportHeaders = [
            $headers['report_id'],
            $headers['name'],
            $headers['accesses'],
            $headers['last_access'],
        ];
        $reportsMostRows = [];
        foreach ($data['reportsMost'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $reportsMostRows[] = [
                $row['report_uuid'] ?? '',
                $row['name'] ?? '',
                $row['total'] ?? 0,
                $row['last_access'] ?? '',
            ];
        }
        $appendSheet($sheetNames['reports_most'], $reportHeaders, $reportsMostRows);

        $reportsLeastRows = [];
        foreach ($data['reportsLeast'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $reportsLeastRows[] = [
                $row['report_uuid'] ?? '',
                $row['name'] ?? '',
                $row['total'] ?? 0,
                $row['last_access'] ?? '',
            ];
        }
        $appendSheet($sheetNames['reports_least'], $reportHeaders, $reportsLeastRows);

        $reportsInactiveHeaders = [
            $headers['report_id'],
            $headers['name'],
            $headers['last_access'],
            $headers['created'],
        ];
        $reportsInactiveRows = [];
        foreach ($data['reportsNoAcc'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $reportsInactiveRows[] = [
                $row['report_uuid'] ?? '',
                $row['name'] ?? '',
                $row['last_access'] ?? '',
                $row['created_at'] ?? '',
            ];
        }
        $appendSheet($sheetNames['reports_inactive'], $reportsInactiveHeaders, $reportsInactiveRows);

        $userProfile = $data['userProfile'] ?? null;
        if (is_array($userProfile)) {
            $profileRows = [];
            $userInfo = $userProfile['user'] ?? [];
            $profileRows[] = [$headers['user_id'], $userInfo['id'] ?? ''];
            $profileRows[] = [$headers['name'], $userInfo['name'] ?? ''];
            $profileRows[] = [$headers['email'], $userInfo['email'] ?? ''];
            $profileRows[] = [$summaryLabels['range_from'], $userProfile['from'] ?? ''];
            $profileRows[] = [$summaryLabels['range_to'], $userProfile['to'] ?? ''];

            $kpis = $userProfile['kpis'] ?? [];
            if (is_array($kpis)) {
                foreach ($kpis as $label => $value) {
                    $profileRows[] = [ucwords(str_replace(['_', '-'], ' ', (string) $label)), $value];
                }
            }
            $appendSheet($sheetNames['profile_summary'], [$headers['key'], $headers['value']], $profileRows);

            $profileHeatmapRows = [];
            foreach ($userProfile['heatmap'] ?? [] as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $profileHeatmapRows[] = [
                    $row['hour'] ?? '',
                    $row[1] ?? 0,
                    $row[2] ?? 0,
                    $row[3] ?? 0,
                    $row[4] ?? 0,
                    $row[5] ?? 0,
                    $row[6] ?? 0,
                    $row[7] ?? 0,
                ];
            }
            if (! empty($profileHeatmapRows)) {
                $appendSheet(
                    $sheetNames['profile_heatmap'],
                    array_merge([$headers['hour']], $weekdays),
                    $profileHeatmapRows
                );
            }

            $profileTopReportsRows = [];
            foreach (collect($userProfile['topReports'] ?? []) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $profileTopReportsRows[] = [$row['label'] ?? '', $row['total'] ?? 0];
            }
            if (! empty($profileTopReportsRows)) {
                $appendSheet($sheetNames['profile_top_reports'], [$headers['report'], $headers['accesses']], $profileTopReportsRows);
            }
        }

        $userEvents = $data['userEvents'] ?? [];
        if (! empty($userEvents)) {
            $eventRows = [];
            foreach ($userEvents as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $eventRows[] = [
                    $row['occurred_at'] ?? '',
                    $row['action'] ?? '',
                    $row['report_uuid'] ?? '',
                    $row['report_name'] ?? '',
                    $row['page_name'] ?? '',
                    $row['bookmark'] ?? '',
                    $row['duration_ms'] ?? null,
                    isset($row['success']) ? ($row['success'] ? __('common.yes') : __('common.no')) : '',
                    $row['error'] ?? '',
                ];
            }
            $appendSheet($sheetNames['profile_events'], [
                $headers['date'],
                $headers['action'],
                $headers['report_id'],
                $headers['report'],
                $headers['page'],
                $headers['bookmark'],
                $headers['duration_ms'],
                $headers['success'],
                $headers['error'],
            ], $eventRows);
        }

        return $sheets;
    }
}
