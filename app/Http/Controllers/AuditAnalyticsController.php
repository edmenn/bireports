<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\User;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AuditAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        // Tab activa
        $tab = (string) $request->input('tab', 'dashboard');

        // Rango por defecto: últimos 60 días (puedes cambiarlo a 30 si prefieres)
        $to   = Carbon::today()->endOfDay();
        $from = (clone $to)->subDays(59)->startOfDay();

        // Override por query
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');
        if (!empty($fromInput)) $from = Carbon::parse((string)$fromInput)->startOfDay();
        if (!empty($toInput))   $to   = Carbon::parse((string)$toInput)->endOfDay();

        // Filtros opcionales
        $reportUuid = $request->filled('report_uuid') ? (string)$request->input('report_uuid') : null;
        $userId     = $request->filled('user_id')     ? (int)$request->input('user_id')       : null;

        $base = Audit::query()
            ->when($reportUuid, fn ($q) => $q->where('report_uuid', $reportUuid))
            ->when($userId,     fn ($q) => $q->where('user_id', $userId))
            ->whereBetween('occurred_at', [$from, $to]);

        // Datos comunes (para tarjetas y dashboard)
        $totals = [
            'totalAccesos'  => (clone $base)->count(),
            'totalUsuarios' => (clone $base)->distinct('user_id')->whereNotNull('user_id')->count('user_id'),
            'totalReportes' => (clone $base)->distinct('report_uuid')->count('report_uuid'),
        ];

        // Dashboard (meses, heatmap, top 10)
        $dashboard = $this->buildDashboard($base, $from, $to);

        // Pestañas de usuarios
        $usersMost   = [];
        $usersLeast  = [];
        $usersNoAct  = [];
        // Pestañas de reportes
        $reportsMost  = [];
        $reportsLeast = [];
        $reportsNoAcc = [];

        switch ($tab) {
            case 'users-most':
                $usersMost = $this->usersRank($base, 'desc', 100);
                break;
            case 'users-least':
                $usersLeast = $this->usersRank($base, 'asc', 100);
                break;
            case 'users-inactive':
                $usersNoAct = $this->usersWithoutAccess($from, $to);
                break;
            case 'reports-most':
                $reportsMost = $this->reportsRank($base, 'desc', 100);
                break;
            case 'reports-least':
                $reportsLeast = $this->reportsRank($base, 'asc', 100);
                break;
            case 'reports-inactive':
                $reportsNoAcc = $this->reportsWithoutAccess($from, $to);
                break;
        }

        $dowLabels = [1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes',6=>'Sábado',7=>'Domingo'];

        return view('auditoria.index', array_merge([
            'tab'          => $tab,
            'from'         => $from->toDateString(),
            'to'           => $to->toDateString(),
            'reportUuid'   => $reportUuid,
            'userId'       => $userId,
            'dowLabels'    => $dowLabels,
        ], $totals, $dashboard, [
            'usersMost'    => $usersMost,
            'usersLeast'   => $usersLeast,
            'usersNoAct'   => $usersNoAct,
            'reportsMost'  => $reportsMost,
            'reportsLeast' => $reportsLeast,
            'reportsNoAcc' => $reportsNoAcc,
        ]));
    }

    /** ======= Dashboard helpers ======= */
    private function buildDashboard($base, Carbon $from, Carbon $to): array
    {
        // Meses
        $monthsRaw = (clone $base)
            ->selectRaw("to_char(date_trunc('month', occurred_at), 'YYYY-MM') AS ym, COUNT(*) AS total")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

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

        // Top usuarios (10)
        $topUsers = (clone $base)
            ->select('user_id', DB::raw('COUNT(*) AS total'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($r) {
                $u = User::query()->find($r->user_id);
                return ['name' => $u?->name ?? ('Usuario #'.$r->user_id), 'total' => (int)$r->total];
            })->values();

        // Top reportes (10)
        $topReports = (clone $base)
            ->select('report_uuid', DB::raw('COUNT(*) AS total'))
            ->groupBy('report_uuid')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($r) => ['label' => (string)$r->report_uuid, 'total' => (int)$r->total])
            ->values();

        // Heatmap día × hora
        $gridRaw = (clone $base)
            ->selectRaw('EXTRACT(ISODOW FROM occurred_at)::int AS dow, EXTRACT(HOUR FROM occurred_at)::int AS hr, COUNT(*) AS total')
            ->groupBy('dow', 'hr')
            ->get();

        $heatmap = [];
        for ($h = 0; $h < 24; $h++) {
            $row = ['hour' => sprintf('%02d:00', $h)];
            for ($d = 1; $d <= 7; $d++) $row[$d] = 0;
            $heatmap[$h] = $row;
        }
        foreach ($gridRaw as $r) $heatmap[$r->hr][$r->dow] = (int)$r->total;

        return [
            'months'     => $months,
            'topUsers'   => $topUsers,
            'topReports' => $topReports,
            'heatmap'    => array_values($heatmap),
        ];
    }

    /** ======= Users tabs ======= */
    private function usersRank($base, string $direction, int $limit): array
    {
        // >=1 acceso en el rango
        $rows = (clone $base)
            ->select('user_id', DB::raw('COUNT(*) AS total'), DB::raw('MAX(occurred_at) AS last_access'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderBy('total', $direction === 'asc' ? 'asc' : 'desc')
            ->limit($limit)
            ->get();

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

    private function usersWithoutAccess(Carbon $from, Carbon $to): array
    {
        // Usuarios sin accesos en el rango
        $sub = Audit::query()
            ->select('user_id')
            ->whereNotNull('user_id')
            ->whereBetween('occurred_at', [$from, $to])
            ->groupBy('user_id');

        $users = User::query()
            ->whereNotIn('id', $sub)
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();

        // Último acceso global (fuera del rango)
        $lastByUser = Audit::query()
            ->select('user_id', DB::raw('MAX(occurred_at) AS last_access'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->pluck('last_access', 'user_id');

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

    /** ======= Reports tabs ======= */
    private function reportsRank($base, string $direction, int $limit): array
    {
        $rows = (clone $base)
            ->select('report_uuid', DB::raw('COUNT(*) AS total'), DB::raw('MAX(occurred_at) AS last_access'))
            ->groupBy('report_uuid')
            ->orderBy('total', $direction === 'asc' ? 'asc' : 'desc')
            ->limit($limit)
            ->get();

        // Intentar traer nombre desde modelo Report (ajusta campo según tu esquema)
        $names = Report::query()->whereIn('id', $rows->pluck('report_uuid'))->pluck('name', 'id');

        return $rows->map(function ($r) use ($names) {
            return [
                'report_uuid' => (string)$r->report_uuid,
                'name'        => $names[$r->report_uuid] ?? $r->report_uuid,
                'total'       => (int)$r->total,
                'last_access' => $r->last_access ? Carbon::parse($r->last_access)->toDateTimeString() : null,
            ];
        })->values()->all();
    }

    private function reportsWithoutAccess(Carbon $from, Carbon $to): array
    {
        // Reportes con 0 accesos en el rango
        $sub = Audit::query()
            ->select('report_uuid')
            ->whereBetween('occurred_at', [$from, $to])
            ->groupBy('report_uuid');

        // Ajusta si tu tabla/clave de Report es distinta
        $reports = Report::query()
            ->whereNotIn('id', $sub)
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get(['id','name','created_at']);

        // Último acceso global (fuera del rango)
        $lastByReport = Audit::query()
            ->select('report_uuid', DB::raw('MAX(occurred_at) AS last_access'))
            ->groupBy('report_uuid')
            ->pluck('last_access', 'report_uuid');

        return $reports->map(function ($r) use ($lastByReport) {
            return [
                'report_uuid' => (string)$r->id,
                'name'        => $r->name ?? $r->id,
                'last_access' => isset($lastByReport[$r->id]) ? Carbon::parse($lastByReport[$r->id])->toDateTimeString() : null,
                'created_at'  => $r->created_at?->toDateTimeString(),
            ];
        })->values()->all();
    }

    /** ======= Export CSV (igual que antes) ======= */
    public function export(Request $request): StreamedResponse
    {
        $to   = $request->filled('to')   ? Carbon::parse((string)$request->input('to'))->endOfDay()   : Carbon::today()->endOfDay();
        $from = $request->filled('from') ? Carbon::parse((string)$request->input('from'))->startOfDay() : (clone $to)->subDays(59)->startOfDay();

        $reportUuid = $request->filled('report_uuid') ? (string)$request->input('report_uuid') : null;
        $userId     = $request->filled('user_id')     ? (int)$request->input('user_id')       : null;

        $base = Audit::query()
            ->when($reportUuid, fn ($q) => $q->where('report_uuid', $reportUuid))
            ->when($userId,     fn ($q) => $q->where('user_id', $userId))
            ->whereBetween('occurred_at', [$from, $to])
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
