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
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AuditAnalyticsController extends Controller
{
    /** ÚNICA acción que cuenta como “acceso”. Según tu dump real. */
    private const ACCESS_ACTION = 'render';

    public function index(Request $request): View
    {
        $tab  = (string) $request->input('tab', 'dashboard');

        // ========= RANGO EN UTC (evita que todo quede fuera por TZ) =========
        $to   = now()->utc()->endOfDay();
        $from = (clone $to)->subDays(59)->startOfDay();

        if ($request->filled('from')) {
            $from = Carbon::parse((string)$request->input('from'), config('app.timezone'))
                ->utc()->startOfDay();
        }
        if ($request->filled('to')) {
            $to = Carbon::parse((string)$request->input('to'), config('app.timezone'))
                ->utc()->endOfDay();
        }
        // ====================================================================

        // Filtro de usuario SOLO en perfil
        $applyUserFilter = ($tab === 'user-profile');
        $userId = ($applyUserFilter && $request->filled('user_id')) ? (int)$request->input('user_id') : null;

        // Base GLOBAL: renders en rango (sin filtrar usuario)
        $baseGlobal = Audit::query()
            ->whereBetween('occurred_at', [$from, $to])
            ->where('action', self::ACCESS_ACTION);

        // Base SCOPED: para tarjetas si estás en perfil
        $baseScoped = (clone $baseGlobal)
            ->when($applyUserFilter && $userId, fn ($q) => $q->where('user_id', $userId));

        // Lista para selector de perfil
        $usersList = User::query()->select('id','name','email')->orderBy('name')->limit(1000)->get();

        // Tarjetas (cabecera)
        $totals = [
            'totalAccesos'  => (clone $baseScoped)->count(),
            'totalUsuarios' => (clone $baseScoped)->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            'totalReportes' => (clone $baseScoped)->distinct('report_uuid')->count('report_uuid'),
        ];

        // Dashboard SIEMPRE GLOBAL
        $dashboard = $this->buildDashboard($baseGlobal, $from, $to);

        // Pestañas
        $usersMost   = [];
        $usersLeast  = [];
        $usersNoAct  = [];

        $reportsMost  = [];
        $reportsLeast = [];
        $reportsNoAcc = [];

        $userProfile = null;
        $userEvents  = [];

        switch ($tab) {
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

        $dowLabels = [1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes',6=>'Sábado',7=>'Domingo'];

        return view('auditoria.index', array_merge([
            'tab'           => $tab,
            'from'          => $from->toDateString(),
            'to'            => $to->toDateString(),
            'userId'        => $userId,
            'usersList'     => $usersList,
            'dowLabels'     => $dowLabels,
            'accessAction'  => self::ACCESS_ACTION,
        ], $totals, $dashboard, [
            'usersMost'     => $usersMost,
            'usersLeast'    => $usersLeast,
            'usersNoAct'    => $usersNoAct,
            'reportsMost'   => $reportsMost,
            'reportsLeast'  => $reportsLeast,
            'reportsNoAcc'  => $reportsNoAcc,
            'userProfile'   => $userProfile,
            'userEvents'    => $userEvents,
        ]));
    }

    /** Dashboard global (renders). */
    private function buildDashboard($base, Carbon $from, Carbon $to): array
    {
        // Meses
        $monthsRaw = (clone $base)
            ->selectRaw("to_char(date_trunc('month', occurred_at), 'YYYY-MM') AS ym, COUNT(*) AS total")
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

        // Heatmap
        $gridRaw = (clone $base)
            ->selectRaw('EXTRACT(ISODOW FROM occurred_at)::int AS dow, EXTRACT(HOUR FROM occurred_at)::int AS hr, COUNT(*) AS total')
            ->groupBy('dow','hr')->get();

        $heatmap = [];
        for ($h = 0; $h < 24; $h++) {
            $row = ['hour' => sprintf('%02d:00', $h)];
            for ($d = 1; $d <= 7; $d++) $row[$d] = 0;
            $heatmap[$h] = $row;
        }
        foreach ($gridRaw as $r) $heatmap[$r->hr][$r->dow] = (int)$r->total;

        // Tendencia diaria
        $dailyRaw = (clone $base)
            ->selectRaw("DATE(occurred_at) AS d, COUNT(*) AS total")
            ->groupBy('d')->orderBy('d')->get()->keyBy('d');

        $daily = [];
        $cur = (clone $from)->startOfDay();
        while ($cur->lte($to)) {
            $k = $cur->toDateString();
            $daily[] = ['date'=>$k, 'total'=> isset($dailyRaw[$k]) ? (int)$dailyRaw[$k]->total : 0];
            $cur->addDay();
        }

        // Top usuarios
        $topUsers = (clone $base)
            ->select('user_id', DB::raw('COUNT(*) AS total'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')->orderByDesc('total')->limit(10)->get()
            ->map(function ($r) {
                $u = User::query()->find($r->user_id);
                return ['name' => $u?->name ?? ('Usuario #'.$r->user_id), 'total' => (int)$r->total];
            })->values();

        // Top reportes
        $topReportsRows = (clone $base)
            ->select('report_uuid', DB::raw('COUNT(*) AS total'))
            ->groupBy('report_uuid')->orderByDesc('total')->limit(10)->get();

        $reportNames = Report::query()->whereIn('id', $topReportsRows->pluck('report_uuid'))->pluck('name','id');
        $topReports  = $topReportsRows->map(function ($r) use ($reportNames) {
            $id = (string)$r->report_uuid;
            return ['label' => $reportNames[$id] ?? $id, 'total' => (int)$r->total];
        })->values();

        // KPIs 7/30 días (renders)
        $since7  = (clone $to)->copy()->subDays(6)->startOfDay();
        $since30 = (clone $to)->copy()->subDays(29)->startOfDay();

        $activeUsers7   = (clone $base)->where('occurred_at','>=',$since7 )->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $activeUsers30  = (clone $base)->where('occurred_at','>=',$since30)->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $activeReports7 = (clone $base)->where('occurred_at','>=',$since7 )->distinct('report_uuid')->count('report_uuid');
        $activeReports30= (clone $base)->where('occurred_at','>=',$since30)->distinct('report_uuid')->count('report_uuid');

        // Errores 7d
        $errors7    = Audit::query()->whereBetween('occurred_at', [$since7, $to])->where('action','error')->count();
        $views7     = (clone $base)->where('occurred_at','>=',$since7)->count();
        $errorRate7 = $views7 > 0 ? round(($errors7 / $views7) * 100, 1) : 0.0;

        // Top errores 30d
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

        // Variación 7d vs previos
        $since7_from = $since7;
        $prev7_from = (clone $since7_from)->subDays(7);
        $prev7_to   = (clone $since7_from)->subDay()->endOfDay();

        $cnt7  = (clone $base)->whereBetween('occurred_at', [$since7, $to])->count();
        $cntp7 = (clone $base)->whereBetween('occurred_at', [$prev7_from, $prev7_to])->count();
        $delta7 = $cntp7 > 0 ? round((($cnt7 - $cntp7) / $cntp7) * 100, 1) : null;

        $alerts = [];
        if ($delta7 !== null && $delta7 <= -30.0) {
            $alerts[] = ['type'=>'warning','msg'=>"Caída de uso ≥30%: últimos 7 días ($cnt7) vs previos ($cntp7) → {$delta7}%."]; }
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
        $rows = (clone $base)
            ->select('user_id', DB::raw('COUNT(*) AS total'), DB::raw('MAX(occurred_at) AS last_access'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderBy('total', $direction === 'asc' ? 'asc' : 'desc')
            ->limit($limit)->get();

        return $rows->map(function ($r) {
            $u = User::query()->find($r->user_id);
            return [
                'user_id'     => (int) $r->user_id,
                'name'        => $u?->name ?? ('Usuario #'.$r->user_id),
                'email'       => $u?->email,
                'total'       => (int)$r->total,
                'last_access' => $r->last_access ? Carbon::parse($r->last_access)->toDateTimeString() : null,
                'created_at'  => $u?->created_at?->toDateTimeString(),
            ];
        })->values()->all();
    }

    /** Usuarios sin renders en rango. */
    private function usersWithoutAccess(Carbon $from, Carbon $to): array
    {
        $sub = Audit::query()
            ->select('user_id')
            ->whereNotNull('user_id')
            ->whereBetween('occurred_at', [$from, $to])
            ->where('action', self::ACCESS_ACTION)
            ->groupBy('user_id');

        $users = User::query()
            ->whereNotIn('id', $sub)
            ->orderBy('created_at', 'desc')
            ->limit(500)->get();

        $lastByUser = Audit::query()
            ->select('user_id', DB::raw('MAX(occurred_at) AS last_access'))
            ->whereNotNull('user_id')
            ->where('action', self::ACCESS_ACTION)
            ->groupBy('user_id')->pluck('last_access', 'user_id');

        return $users->map(function ($u) use ($lastByUser) {
            return [
                'user_id'     => (int)$u->id,
                'name'        => $u->name,
                'email'       => $u->email,
                'last_access' => isset($lastByUser[$u->id]) ? Carbon::parse($lastByUser[$u->id])->toDateTimeString() : null,
                'created_at'  => $u->created_at?->toDateTimeString(),
            ];
        })->values()->all();
    }

    /** Rankings Reportes (renders). */
    private function reportsRank($base, string $direction, int $limit): array
    {
        $rows = (clone $base)
            ->select('report_uuid', DB::raw('COUNT(*) AS total'), DB::raw('MAX(occurred_at) AS last_access'))
            ->groupBy('report_uuid')
            ->orderBy('total', $direction === 'asc' ? 'asc' : 'desc')
            ->limit($limit)->get();

        $names = Report::query()->whereIn('id', $rows->pluck('report_uuid'))->pluck('name', 'id');

        return $rows->map(function ($r) use ($names) {
            $id = (string)$r->report_uuid;
            return [
                'report_uuid' => $id,
                'name'        => $names[$id] ?? $id,
                'total'       => (int)$r->total,
                'last_access' => $r->last_access ? Carbon::parse($r->last_access)->toDateTimeString() : null,
            ];
        })->values()->all();
    }

    /** Reportes sin renders en rango. */
    private function reportsWithoutAccess(Carbon $from, Carbon $to): array
    {
        $sub = Audit::query()
            ->select('report_uuid')
            ->whereBetween('occurred_at', [$from, $to])
            ->where('action', self::ACCESS_ACTION)
            ->groupBy('report_uuid');

        $reports = Report::query()
            ->whereNotIn('id', $sub)
            ->orderBy('created_at', 'desc')
            ->limit(500)->get(['id','name','created_at']);

        $lastByReport = Audit::query()
            ->select('report_uuid', DB::raw('MAX(occurred_at) AS last_access'))
            ->where('action', self::ACCESS_ACTION)
            ->groupBy('report_uuid')->pluck('last_access', 'report_uuid');

        return $reports->map(function ($r) use ($lastByReport) {
            $id = (string)$r->id;
            return [
                'report_uuid' => $id,
                'name'        => $r->name ?? $id,
                'last_access' => isset($lastByReport[$id]) ? Carbon::parse($lastByReport[$id])->toDateTimeString() : null,
                'created_at'  => $r->created_at?->toDateTimeString(),
            ];
        })->values()->all();
    }

    /** PERFIL: KPIs y heatmap sobre renders; timeline con TODAS las acciones + report_name. */
    private function buildUserProfile(int $userId, Carbon $from, Carbon $to): array
    {
        $user = User::query()->find($userId);
        if (!$user) return [null, []];

        $base = Audit::query()
            ->where('user_id', $userId)
            ->whereBetween('occurred_at', [$from, $to])
            ->where('action', self::ACCESS_ACTION);

        // KPIs del usuario
        $totalAccesos   = (clone $base)->count();
        $lastAccess     = (clone $base)->max('occurred_at');
        $reportesUnicos = (clone $base)->distinct('report_uuid')->count('report_uuid');
        $diasActivos    = (clone $base)->selectRaw("DATE(occurred_at) AS d")->groupBy('d')->get()->count();

        // Top reportes del usuario
        $topReportsRows = (clone $base)
            ->select('report_uuid', DB::raw('COUNT(*) AS total'))
            ->groupBy('report_uuid')->orderByDesc('total')->limit(10)->get();

        $reportNames = Report::query()->whereIn('id', $topReportsRows->pluck('report_uuid'))->pluck('name','id');
        $topReports  = $topReportsRows->map(function ($r) use ($reportNames) {
            $id = (string)$r->report_uuid;
            return ['label' => $reportNames[$id] ?? $id, 'total' => (int)$r->total];
        })->values();

        // Heatmap del usuario
        $gridRaw = (clone $base)
            ->selectRaw('EXTRACT(ISODOW FROM occurred_at)::int AS dow, EXTRACT(HOUR FROM occurred_at)::int AS hr, COUNT(*) AS total')
            ->groupBy('dow','hr')->get();

        $heatmap = [];
        for ($h = 0; $h < 24; $h++) {
            $row = ['hour' => sprintf('%02d:00', $h)];
            for ($d = 1; $d <= 7; $d++) $row[$d] = 0;
            $heatmap[$h] = $row;
        }
        foreach ($gridRaw as $r) $heatmap[$r->hr][$r->dow] = (int)$r->total;

        // Timeline: TODAS las acciones del usuario (hasta 500) + nombre de reporte
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
            ->map(function ($e) use ($nameMap) {
                $rid = (string)$e->report_uuid;
                return [
                    'occurred_at' => Carbon::parse($e->occurred_at)->toDateTimeString(),
                    'action'      => (string)$e->action,
                    'report_uuid' => $rid,
                    'report_name' => $nameMap[$rid] ?? $rid, // SIEMPRE presente
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
                'ultimoAcceso'   => $lastAccess ? Carbon::parse($lastAccess)->toDateTimeString() : null,
                'reportesUnicos' => $reportesUnicos,
                'diasActivos'    => $diasActivos,
            ],
            'topReports'     => $topReports,
            'heatmap'        => array_values($heatmap),
        ];

        return [$profile, $events];
    }

    /** Export CSV (renders) — también en UTC. */
    public function export(Request $request): StreamedResponse
    {
        $to   = $request->filled('to')
            ? Carbon::parse((string)$request->input('to'), config('app.timezone'))->utc()->endOfDay()
            : now()->utc()->endOfDay();

        $from = $request->filled('from')
            ? Carbon::parse((string)$request->input('from'), config('app.timezone'))->utc()->startOfDay()
            : (clone $to)->subDays(59)->startOfDay();

        $tab    = (string) $request->input('tab', 'dashboard');
        $userId = ($tab === 'user-profile' && $request->filled('user_id')) ? (int)$request->input('user_id') : null;

        $base = Audit::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->whereBetween('occurred_at', [$from, $to])
            ->where('action', self::ACCESS_ACTION)
            ->orderBy('occurred_at');

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=auditoria.csv',
        ];

        return response()->stream(function() use ($base) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'id','occurred_at','user_id','report_uuid','workspace_uuid','dataset_uuid',
                'action','page_name','rls_role','duration_ms','success','ip','referer','user_agent','filters','error_message'
            ]);
            $base->chunk(1000, function($chunk) use ($out) {
                foreach ($chunk as $r) {
                    fputcsv($out, [
                        $r->id, $r->occurred_at, $r->user_id, $r->report_uuid, $r->workspace_uuid, $r->dataset_uuid,
                        $r->action, $r->page_name, $r->rls_role, $r->duration_ms, $r->success ? 1 : 0,
                        $r->ip, $r->referer, $r->user_agent ? mb_substr($r->user_agent,0,200) : null,
                        $r->filters ? json_encode($r->filters, JSON_UNESCAPED_UNICODE) : null, $r->error_message,
                    ]);
                }
            });
            fclose($out);
        }, 200, $headers);
    }
}
