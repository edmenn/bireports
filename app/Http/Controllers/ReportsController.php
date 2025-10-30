<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Capacity; // Minimal mapping between workspace.capacity_id (UUID) and the local PK
use App\Models\Workspace;
use App\Services\PowerBiService;
use App\Services\ReportAccessService;
use App\Models\Configuration;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ReportsController extends Controller
{
    public function __construct(private readonly ReportAccessService $access)
    {
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $reports = Report::query()
            ->with('workspace')
            ->when($q !== '', function ($query) use ($q) {
                $needle = mb_strtolower($q);
                $query->where(function ($w) use ($needle) {
                    $w->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"]) 
                      ->orWhereRaw('LOWER(id) LIKE ?', ["%{$needle}%"]) 
                      ->orWhereRaw('LOWER(workspace_id) LIKE ?', ["%{$needle}%"]) 
                      ->orWhereRaw('LOWER(dataset_id) LIKE ?', ["%{$needle}%"]);
                });
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('reports.index', [
            'reports' => $reports,
            'q'       => $q,
        ]);
    }

    /**
     * Render the embedded report ensuring it belongs to the workspace in the route.
     * If generating the embed token fails, render the view with 'error' and 'error_debug' instead of throwing.
     */
    public function show(string $workspaceId, string $reportId, Request $request): View
    {
        // 1) Load workspace (404 when missing)
        $workspace = Workspace::findOrFail($workspaceId);

        // 2) Load the report belonging to that workspace (404 otherwise)
        $report = Report::where('id', $reportId)
            ->where('workspace_id', $workspace->id)
            ->firstOrFail();

        $user = $request->user();
        if ($user && ! $user->hasRole('admin') && ! $this->access->userCanAccessReport($user, $report)) {
            abort(403);
        }

        // Optional capacity mapping required for warm-up endpoints
        // - workspace.capacity_id stores the Power BI/Fabric UUID
        // - /api/capacities/{capacity}/ping expects the local PK (integer)
        //   Map it here so the view can trigger ping/resume on demand.
        $capacityModelId = null;
        $capacityUuid    = (string) ($workspace->capacity_id ?? '');
        if ($capacityUuid !== '') {
            $capModel = Capacity::query()->where('capacity_uuid', $capacityUuid)->first();
            $capacityModelId = $capModel?->id;
        }

        // 3) Optional RLS parameters from the query string
        $rlsUser  = $request->query('rls_user');
        $rlsRoles = array_values(array_filter(array_map('trim', explode(',', (string) $request->query('rls_roles', '')))));

        $embedArr  = [];
        $errShort  = null;
        $errDetail = null;

        // 4) When configuration is incomplete, return a friendly error view
        $hasConfig = Configuration::query()
            ->whereNotNull('powerbi_tenant_id')->where('powerbi_tenant_id', '!=', '')
            ->whereNotNull('powerbi_client_id')->where('powerbi_client_id', '!=', '')
            ->whereNotNull('powerbi_client_secret')->where('powerbi_client_secret', '!=', '')
            ->exists();

        if (!$hasConfig) {
            $errShort  = __('reports.show.errors.no_credentials');
            $embedArr  = [];
        } else {
            try {
                // 4) Generate an embed token
                $svc = app(PowerBiService::class);
                $embed = $svc->generateReportEmbedToken(
                workspaceId: $workspace->id,
                reportId:    $report->id,
                datasetId:   $report->dataset_id,
                rlsUser:     $rlsUser ?: null,
                rlsRoles:    $rlsRoles
            );

                // 4.1) Normalise keys expected by the view
                $embedArr = is_array($embed) ? $embed : (array) $embed;

                $embedArr['accessToken'] = $embedArr['accessToken']
                    ?? ($embedArr['token'] ?? null)
                    ?? ($embedArr['access_token'] ?? null);

                $embedArr['embedUrl']    = $embedArr['embedUrl']    ?? ($embedArr['embed_url'] ?? null);
                $embedArr['reportId']    = $embedArr['reportId']    ?? $report->id;
                $embedArr['tokenType']   = $embedArr['tokenType']   ?? 'Embed';
                $embedArr['datasetId']   = $embedArr['datasetId']   ?? $report->dataset_id;
                $embedArr['workspaceId'] = $embedArr['workspaceId'] ?? $workspace->id;
            } catch (Throwable $e) {
                // 5) Fail fast without redirecting to an error page; render a banner instead
                $errShort  = __('reports.show.errors.embed_failure');
                // Provide a safe diagnostic payload (no secrets exposed; include class, message, stack)
                $errDetail = __('reports.show.errors.debug_template', [
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                    'code'      => (string) ($e->getCode() ?? '0'),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                    'trace'     => $e->getTraceAsString(),
                ]);
                // Return with an empty embed array so the view renders diagnostics without breaking
                $embedArr = [];
            }
        }

        // 5.5) Minimal warm-up parameters consumed by the view
        $cfg = Configuration::first();
        $capacityOnDemandEnable = (bool) ($cfg?->capacity_on_demand_enable ?? true);
        $capacityOnDemandMessage = (string) ($cfg?->capacity_on_demand_message ?? __('reports.show.on_demand.starting'));
        $capacityStartTimeoutSec = (int) ($cfg?->capacity_start_timeout_sec ?? 120);
        $capacityStartPollSec    = (int) ($cfg?->capacity_start_poll_interval_sec ?? 5);
        $reportLoadTimeoutSec    = (int) ($cfg?->report_load_timeout_sec ?? 10);

        // 6) Render view
        return view('reports.show', [
            'report'       => $report,
            'workspace'    => $workspace,
            'embedded'     => $request->boolean('embedded'),
            'embed'        => $embedArr,
            'error'        => $errShort,   // short banner message
            'error_debug'  => $errDetail,  // <details> payload for copy/paste
            // Capacity identifiers used by on-demand warm-up
            'capacityModelId' => $capacityModelId, // Local PK for /api/capacities/{id}/ping
            'capacityUuid'    => $capacityUuid,    // Fabric/PBI UUID (informational/logging)
            // On-demand parameters
            'capacityOnDemandEnable' => $capacityOnDemandEnable,
            'capacityOnDemandMessage' => $capacityOnDemandMessage,
            'capacityStartTimeoutSec' => $capacityStartTimeoutSec,
            'capacityStartPollSec'    => $capacityStartPollSec,
            'reportLoadTimeoutSec'    => $reportLoadTimeoutSec,
        ]);
    }

    public function edit(Report $report): View
    {
        return view('reports.edit', [
            'report' => $report,
        ]);
    }

    public function update(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'description'  => ['nullable', 'string', 'max:2000'],
            'cover_url'    => ['nullable', 'url', 'max:2048'],
            'cover_file'   => ['nullable','file','max:2048','mimetypes:image/jpeg,image/png,image/webp,image/gif,image/svg+xml'],
            'cover_scale'  => ['nullable','numeric','between:0.5,1.5'],
            'cover_scale_x'=> ['nullable','numeric','between:0.5,1.5'],
            'cover_scale_y'=> ['nullable','numeric','between:0.5,1.5'],
            'remove_cover' => ['nullable','boolean'],
        ], [
            'cover_url.url'        => __('reports.validation.cover_url_url'),
            'cover_file.max'       => __('reports.validation.cover_file_max'),
            'cover_file.mimetypes' => __('reports.validation.cover_file_mimetypes'),
            'cover_scale.between'  => __('reports.validation.cover_scale_between'),
            'cover_scale.numeric'  => __('reports.validation.cover_scale_numeric'),
            'cover_scale_x.between'=> __('reports.validation.cover_scale_between'),
            'cover_scale_x.numeric'=> __('reports.validation.cover_scale_numeric'),
            'cover_scale_y.between'=> __('reports.validation.cover_scale_between'),
            'cover_scale_y.numeric'=> __('reports.validation.cover_scale_numeric'),
            'remove_cover.boolean'=> __('validation.boolean', ['attribute' => 'remove_cover']),
        ]);

        $removeCover = $request->boolean('remove_cover');
        $originalCoverUrl = $report->cover_url;

        // --- Uploaded image takes priority ---
        if ($request->hasFile('cover_file')) {
            $file = $request->file('cover_file');

            // Store under storage/app/public/reports
            $path = $file->store('reports', 'public');
            $publicUrl = asset('storage/'.$path);

            $report->cover_url = $publicUrl;

            $removeCover = false;
        }
        // --- Otherwise accept a manual URL ---
        elseif (!$removeCover && array_key_exists('cover_url', $validated)) {
            $url = trim((string) ($validated['cover_url'] ?? ''));
            $report->cover_url = $url !== '' ? $url : null;
            if ($report->cover_url !== null) {
                $removeCover = false;
            }
        }

        if ($removeCover && !$request->hasFile('cover_file')) {
            if (!empty($originalCoverUrl) && Str::startsWith($originalCoverUrl, asset('storage/'))) {
                $relative = Str::after($originalCoverUrl, asset('storage/'));
                if ($relative !== '') {
                    Storage::disk('public')->delete($relative);
                }
            }
            $report->cover_url = null;
            $report->cover_scale_x = 1.0;
            $report->cover_scale_y = 1.0;
            $report->cover_scale   = 1.0;
        }

        // --- Description ---
        if (array_key_exists('description', $validated)) {
            $desc = trim((string) ($validated['description'] ?? ''));
            $report->description = $desc !== '' ? $desc : null;
        }

        // --- Allow resume outside schedule window ---
        if ($request->has('allow_resume_outside_window')) {
            $report->allow_resume_outside_window = $request->boolean('allow_resume_outside_window');
        }

        if ($request->hasAny(['cover_scale', 'cover_scale_x', 'cover_scale_y'])) {
            $scaleX = $request->input('cover_scale_x', $request->input('cover_scale', $report->cover_scale_x ?? $report->cover_scale ?? 1));
            $scaleY = $request->input('cover_scale_y', $request->input('cover_scale', $report->cover_scale_y ?? $report->cover_scale ?? 1));
            $scaleX = min(1.5, max(0.5, (float) $scaleX));
            $scaleY = min(1.5, max(0.5, (float) $scaleY));

            $report->cover_scale_x = $scaleX;
            $report->cover_scale_y = $scaleY;
            // Maintain legacy column for backward compatibility.
            $report->cover_scale   = $scaleX;
        }

        $report->save();

        return redirect()
            ->route('reports.edit', $report)
            ->with('status', __('reports.flash.updated'));
    }
}
