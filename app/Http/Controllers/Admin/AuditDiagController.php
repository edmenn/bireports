<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class AuditDiagController extends Controller
{
    public function index(Request $request): View
    {
        $to   = $request->filled('to')   ? Carbon::parse((string)$request->input('to'))->endOfDay()   : Carbon::today()->endOfDay();
        $from = $request->filled('from') ? Carbon::parse((string)$request->input('from'))->startOfDay() : (clone $to)->subDays(89)->startOfDay();

        $globalMin = Audit::query()->min('occurred_at');
        $globalMax = Audit::query()->max('occurred_at');
        $totalAll  = Audit::query()->count();

        $byAction = Audit::query()
            ->whereBetween('occurred_at', [$from, $to])
            ->select('action', DB::raw('COUNT(*) AS total'))
            ->groupBy('action')->orderByDesc('total')->get();

        $distinctActions = Audit::query()
            ->select('action')->distinct()->orderBy('action')->pluck('action')->all();

        $candidates = ['open','loaded','rendered','page_changed','filters_applied','bookmark_applied','data_selected','error'];
        $candidateCounts = [];
        foreach ($candidates as $a) {
            $candidateCounts[$a] = Audit::query()
                ->whereBetween('occurred_at', [$from, $to])
                ->where('action', $a)
                ->count();
        }

        $latest = Audit::query()
            ->whereBetween('occurred_at', [$from, $to])
            ->orderByDesc('occurred_at')
            ->limit(100)
            ->get([
                'id','occurred_at','user_id','report_uuid','workspace_uuid','dataset_uuid',
                'action','page_name','duration_ms','success','ip','referer'
            ]);

        $futureCount = Audit::query()->where('occurred_at', '>', Carbon::now()->addDay())->count();
        $oldCount    = Audit::query()->where('occurred_at', '<', Carbon::now()->subYears(5))->count();

        return view('admin.auditoria_diag', [
            'from'            => $from->toDateString(),
            'to'              => $to->toDateString(),
            'globalMin'       => $globalMin,
            'globalMax'       => $globalMax,
            'totalAll'        => $totalAll,
            'byAction'        => $byAction,
            'distinctActions' => $distinctActions,
            'candidateCounts' => $candidateCounts,
            'latest'          => $latest,
            'futureCount'     => $futureCount,
            'oldCount'        => $oldCount,
        ]);
    }
}
