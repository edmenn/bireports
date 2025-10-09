<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Folder;
use App\Models\Report;
use App\Models\Capacity;
use App\Models\Configuracion;
use App\Services\PowerBiService;
use Throwable;
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

        return back()->with('success', $favorited ? __('portal.favorites.flash.added') : __('portal.favorites.flash.removed'));
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

        $workspace               = null;
        $embed                   = [];
        $errorShort              = null;
        $errorDetail             = null;
        $capacityModelId         = null;
        $capacityUuid            = null;
        $capacityOnDemandEnable  = true;
        $capacityOnDemandMessage = __('reports.show.on_demand.starting');
        $capacityStartTimeoutSec = 120;
        $capacityStartPollSec    = 5;
        $reportLoadTimeoutSec    = 10;

        if ($currentReport) {
            $currentReport->loadMissing('workspace');
            $workspace = $currentReport->workspace;

            if (! $workspace) {
                $errorShort = __('applications.runtime.errors.no_workspace');
            } else {
                $capacityUuid = (string) ($workspace->capacity_id ?? '');
                if ($capacityUuid !== '') {
                    $capModel = Capacity::query()->where('capacity_uuid', $capacityUuid)->first();
                    $capacityModelId = $capModel?->id;
                }

                $cfg = Configuracion::first();
                if ($cfg) {
                    $capacityOnDemandEnable  = (bool) ($cfg->capacity_on_demand_enable ?? true);
                    $capacityOnDemandMessage = (string) ($cfg->capacity_on_demand_message ?? __('reports.show.on_demand.starting'));
                    $capacityStartTimeoutSec = (int) ($cfg->capacity_start_timeout_sec ?? 120);
                    $capacityStartPollSec    = (int) ($cfg->capacity_start_poll_interval_sec ?? 5);
                    $reportLoadTimeoutSec    = (int) ($cfg->report_load_timeout_sec ?? 10);
                }

                $hasConfig = Configuracion::query()
                    ->whereNotNull('powerbi_tenant_id')->where('powerbi_tenant_id', '!=', '')
                    ->whereNotNull('powerbi_client_id')->where('powerbi_client_id', '!=', '')
                    ->whereNotNull('powerbi_client_secret')->where('powerbi_client_secret', '!=', '')
                    ->exists();

                if (! $hasConfig) {
                    $errorShort = __('reports.show.errors.no_credentials');
                } else {
                    try {
                        $svc = app(PowerBiService::class);
                        $embed = $svc->generateReportEmbedToken(
                            workspaceId: $workspace->id,
                            reportId:    $currentReport->id,
                            datasetId:   $currentReport->dataset_id,
                            rlsUser:     null,
                            rlsRoles:    []
                        );

                        $embed = is_array($embed) ? $embed : (array) $embed;
                        $embed['accessToken'] = $embed['accessToken'] ?? ($embed['token'] ?? null) ?? ($embed['access_token'] ?? null);
                        $embed['embedUrl']    = $embed['embedUrl']    ?? ($embed['embed_url'] ?? null);
                        $embed['reportId']    = $embed['reportId']    ?? $currentReport->id;
                        $embed['tokenType']   = $embed['tokenType']   ?? 'Embed';
                        $embed['datasetId']   = $embed['datasetId']   ?? $currentReport->dataset_id;
                        $embed['workspaceId'] = $embed['workspaceId'] ?? $workspace->id;
                    } catch (Throwable $e) {
                        $errorShort = __('reports.show.errors.embed_failure');
                        $errorDetail = __('reports.show.errors.debug_template', [
                            'exception' => get_class($e),
                            'message' => $e->getMessage(),
                            'code' => (string) ($e->getCode() ?? '0'),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        $embed = [];
                    }
                }
            }
        }

        return view('portal.application', [
            'application'             => $application,
            'sections'                => $sections,
            'looseReports'            => $looseReports,
            'currentReport'           => $currentReport,
            'workspace'               => $workspace,
            'embed'                   => $embed,
            'error'                   => $errorShort,
            'error_debug'             => $errorDetail,
            'capacityModelId'         => $capacityModelId,
            'capacityUuid'            => $capacityUuid,
            'capacityOnDemandEnable'  => $capacityOnDemandEnable,
            'capacityOnDemandMessage' => $capacityOnDemandMessage,
            'capacityStartTimeoutSec' => $capacityStartTimeoutSec,
            'capacityStartPollSec'    => $capacityStartPollSec,
            'reportLoadTimeoutSec'    => $reportLoadTimeoutSec,
        ]);
    }
}

