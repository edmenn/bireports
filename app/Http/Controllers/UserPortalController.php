<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Folder;
use App\Models\Report;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserPortalController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $folders = Folder::query()
            ->withCount(['users', 'groups', 'reports'])
            ->where(function ($q) use ($user) {
                $q->whereHas('users', fn ($w) => $w->where('users.id', $user->id))
                    ->orWhereHas('groups.users', fn ($w) => $w->where('users.id', $user->id));
            })
            ->orderBy('name')
            ->get();

        $selectedId = (int) $request->query('carpeta_id', 0);
        $selected = $folders->firstWhere('id', $selectedId) ?? $folders->first();

        $reports = collect();
        if ($selected) {
            $reports = $selected->reports()->orderBy('name')->get();
        }

        $favIds = $user->favoriteReports()->pluck('reports.id')->all();

        return view('portal.index', [
            'folders'  => $folders,
            'selected' => $selected,
            'reports'  => $reports,
            'favIds'   => $favIds,
        ]);
    }

    public function toggleFavorite(Request $request, Report $report)
    {
        $user = Auth::user();
        $exists = $user->favoriteReports()->where('reports.id', $report->id)->exists();
        $favorited = false;
        if ($exists) {
            $user->favoriteReports()->detach($report->id);
            $favorited = false;
        } else {
            $user->favoriteReports()->attach($report->id);
            $favorited = true;
        }

        if ($request->ajax() || $request->wantsJson() || strtolower((string) $request->header('X-Requested-With')) === 'xmlhttprequest') {
            return response()->json(['favorited' => $favorited]);
        }

        return back()->with('success', $favorited ? 'Agregado a favoritos' : 'Quitado de favoritos');
    }

    public function favorites(): View
    {
        $user = Auth::user();
        $reports = $user->favoriteReports()->orderBy('name')->paginate(24)->withQueryString();
        $favIds = $reports->pluck('id')->all();

        return view('portal.favoritos', [
            'reports' => $reports,
            'favIds'  => $favIds,
        ]);
    }

    public function mostAccessed(): View
    {
        $userId = Auth::id();
        $rows = DB::table('audits')
            ->select('report_uuid as id', DB::raw('COUNT(*) as total'))
            ->where('user_id', $userId)
            ->where('action', 'view')
            ->groupBy('report_uuid')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(50)
            ->get();

        $ids = $rows->pluck('id')->all();
        $reports = Report::query()->whereIn('id', $ids)->get()->keyBy('id');

        $list = $rows->map(function ($r) use ($reports) {
            $rep = $reports->get($r->id);
            return [
                'id'           => $r->id,
                'name'         => $rep->name ?? $r->id,
                'total'        => (int) $r->total,
                'workspace_id' => $rep->workspace_id ?? null,
            ];
        });

        return view('portal.mas_accedidos', ['items' => $list]);
    }

    public function apps(Request $request): View
    {
        $user = $request->user();

        $applications = Application::accessibleFor($user)
            ->withCount(['reports', 'sections'])
            ->orderBy('name')
            ->get();

        return view('portal.aplicaciones', [
            'applications' => $applications,
        ]);
    }

    public function application(Request $request, Application $application): View
    {
        $user = $request->user();

        abort_unless($application->is_active, 404);

        $canAccess = Application::accessibleFor($user)
            ->whereKey($application->getKey())
            ->exists();

        if (! $canAccess) {
            abort(403);
        }

        $sections = $application->sections()
            ->with(['reports' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();

        $looseReports = $application->reports()
            ->wherePivotNull('section_id')
            ->orderBy('reports.name')
            ->get();

        $orderedReports = collect();

        foreach ($sections as $section) {
            foreach ($section->reports as $report) {
                $orderedReports->push($report);
            }
        }

        foreach ($looseReports as $report) {
            $orderedReports->push($report);
        }

        $currentReportId = (string) $request->query('report');
        $currentReport = $orderedReports->firstWhere('id', $currentReportId) ?? $orderedReports->first();

        $iframeUrl = null;
        if ($currentReport) {
            $iframeUrl = route('reports.show', [
                'workspace' => $currentReport->workspace_id,
                'report'    => $currentReport->id,
                'embedded'  => 1,
            ]);
        }

        return view('portal.application', [
            'application'   => $application,
            'sections'      => $sections,
            'looseReports'  => $looseReports,
            'currentReport' => $currentReport,
            'iframeUrl'     => $iframeUrl,
        ]);
    }
}

