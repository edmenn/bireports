<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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

        // Carpetas a las que tiene acceso (por usuario o por grupo)
        $folders = Folder::query()
            ->withCount(['users','groups','reports'])
            ->where(function ($q) use ($user) {
                $q->whereHas('users', fn($w)=> $w->where('users.id', $user->id))
                  ->orWhereHas('groups.users', fn($w)=> $w->where('users.id', $user->id));
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
            'folders'   => $folders,
            'selected'  => $selected,
            'reports'   => $reports,
            'favIds'    => $favIds,
        ]);
    }

    public function toggleFavorite(Report $report): RedirectResponse
    {
        $user = Auth::user();
        $exists = $user->favoriteReports()->where('reports.id', $report->id)->exists();
        if ($exists) {
            $user->favoriteReports()->detach($report->id);
            return back()->with('success', 'Quitado de favoritos');
        }
        $user->favoriteReports()->attach($report->id);
        return back()->with('success', 'Agregado a favoritos');
    }

    public function favorites(): View
    {
        $user = Auth::user();
        $reports = $user->favoriteReports()->orderBy('name')->paginate(24)->withQueryString();
        return view('portal.favoritos', compact('reports'));
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
                'id'    => $r->id,
                'name'  => $rep->name ?? $r->id,
                'total' => (int) $r->total,
                'workspace_id' => $rep->workspace_id ?? null,
            ];
        });

        return view('portal.mas_accedidos', ['items' => $list]);
    }

    public function myReports(): View
    {
        $user = Auth::user();

        // Reportes que puede ver por pertenecer a carpetas accesibles
        $folderIds = Folder::query()
            ->where(function ($q) use ($user) {
                $q->whereHas('users', fn($w)=> $w->where('users.id', $user->id))
                  ->orWhereHas('groups.users', fn($w)=> $w->where('users.id', $user->id));
            })
            ->pluck('id');

        $reportIds = DB::table('folder_report')
            ->whereIn('folder_id', $folderIds)
            ->pluck('report_id')
            ->unique()
            ->values();

        $reports = Report::query()->whereIn('id', $reportIds)->orderBy('name')->paginate(24)->withQueryString();
        $favIds = $user->favoriteReports()->pluck('reports.id')->all();

        return view('portal.mis_reportes', compact('reports','favIds'));
    }
}

