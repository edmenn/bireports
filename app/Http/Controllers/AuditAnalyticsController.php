<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditAnalyticsController extends Controller
{
    /**
     * Dashboard de auditoría: métricas clave.
     */
    public function index(Request $request): View
    {
        // Rango por defecto: últimos 30 días (incluye hoy)
        $to   = Carbon::today()->endOfDay();
        $from = (clone $to)->subDays(29)->startOfDay();

        // Permitir override via query ?from=YYYY-MM-DD&to=YYYY-MM-DD (usar input() para evitar Stringable)
        $fromInput = $request->input('from'); // string|null
        $toInput   = $request->input('to');   // string|null

        if (!empty($fromInput)) {
            $from = Carbon::parse((string)$fromInput)->startOfDay();
        }
        if (!empty($toInput)) {
            $to = Carbon::parse((string)$toInput)->endOfDay();
        }

        // Filtros opcionales
        $reportUuid = $request->filled('report_uuid') ? (string)$request->input('report_uuid') : null;
        $userId     = $request->filled('user_id') ? (int)$request->input('user_id') : null;

        $base = Audit::query()
            ->when($reportUuid, fn ($q) => $q->where('report_uuid', $reportUuid))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->whereBetween('occurred_at', [$from, $to]);

        // ===== Top 10 reportes =====
        $topReports = (clone $base)
            ->select('report_uuid', DB::raw('COUNT(*) AS total'))
            ->groupBy('report_uuid')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'report_uuid' => (string) $r->report_uuid,
                'total'       => (int) $r->total,
            ])
            ->values();

        // ===== Top 10 usuarios =====
        $topUsers = (clone $base)
            ->select('user_id', DB::raw('COUNT(*) AS total'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($r) {
                $user = \App\Models\User::query()->find($r->user_id);
                return [
                    'user_id' => (int) $r->user_id,
                    'name'    => $user?->name ?? 'Usuario #'.$r->user_id,
                    'email'   => $user?->email ?? null,
                    'total'   => (int) $r->total,
                ];
            })
            ->values();

        // ===== Por día de la semana (1=Lun..7=Dom) =====
        $dowRaw = (clone $base)
            ->selectRaw('EXTRACT(ISODOW FROM occurred_at) AS dow_iso, COUNT(*) AS total')
            ->groupBy('dow_iso')
            ->orderBy('dow_iso')
            ->get()
            ->keyBy(fn ($r) => (int) $r->dow_iso);

        $dow = [];
        for ($i = 1; $i <= 7; $i++) {
            $dow[$i] = [
                'dow_iso' => $i,
                'label'   => $this->dowLabel($i),
                'total'   => isset($dowRaw[$i]) ? (int) $dowRaw[$i]->total : 0,
            ];
        }
        $dow = array_values($dow);

        // ===== Serie diaria (sin huecos) =====
        $dailyRaw = (clone $base)
            ->selectRaw("DATE(occurred_at) AS d, COUNT(*) AS total")
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy(fn ($r) => (string) $r->d);

        $daily = [];
        $cursor = (clone $from)->startOfDay();
        while ($cursor->lte($to)) {
            $key = $cursor->toDateString(); // YYYY-MM-DD
            $daily[] = [
                'date'  => $key,
                'total' => isset($dailyRaw[$key]) ? (int) $dailyRaw[$key]->total : 0,
            ];
            $cursor->addDay();
        }

        return view('auditoria.index', [
            'from'       => $from->toDateString(),
            'to'         => $to->toDateString(),
            'reportUuid' => $reportUuid,
            'userId'     => $userId,
            'topReports' => $topReports,
            'topUsers'   => $topUsers,
            'dow'        => $dow,
            'daily'      => $daily,
        ]);
    }

    private function dowLabel(int $iso): string
    {
        return match ($iso) {
            1 => 'Lun',
            2 => 'Mar',
            3 => 'Mié',
            4 => 'Jue',
            5 => 'Vie',
            6 => 'Sáb',
            7 => 'Dom',
            default => (string) $iso,
        };
    }
}
