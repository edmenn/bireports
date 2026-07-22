<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\PowerBiService; // Existing service that handles AAD token retrieval
use App\Services\ReportAccessService;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ReportViewerController extends Controller
{
    public function __construct(
        private readonly PowerBiService $pbi,
        private readonly ReportAccessService $access
    )
    {
        // Authentication is enforced at the route level (see web.php)
    }

    /**
     * Render the embedded report view with the variables required by the JS layer.
     * Expects: report_uuid (route), workspace_uuid (query).
     */
    public function show(Request $request, string $report_uuid): View
    {
        $v = Validator::make(
            array_merge($request->all(), ['report_uuid' => $report_uuid]),
            [
                'report_uuid'    => 'required|uuid',
                'workspace_uuid' => 'required|uuid',  // necesitamos groupId para el embed
            ]
        )->validate();

        $reportUuid    = (string) $v['report_uuid'];
        $workspaceUuid = (string) $v['workspace_uuid'];

        $report = Report::query()->with('workspace')->findOrFail($reportUuid);

        if ($report->workspace_id !== $workspaceUuid) {
            abort(404);
        }

        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if (! $user->hasRole('admin') && ! $this->access->userCanAccessReport($user, $report)) {
            abort(403);
        }

        $datasetUuid = $report->dataset_id;

        // When configuration is incomplete, render the view with empty data (avoid 500s)
        $hasConfig = Configuration::query()
            ->whereNotNull('powerbi_tenant_id')->where('powerbi_tenant_id', '!=', '')
            ->whereNotNull('powerbi_client_id')->where('powerbi_client_id', '!=', '')
            ->whereNotNull('powerbi_client_secret')->where('powerbi_client_secret', '!=', '')
            ->exists();

        $embed = [];
        if (! $hasConfig) {
            $accessToken = null;
        } else {
            $embed = $this->pbi->generateReportEmbedToken(
                workspaceId: $workspaceUuid,
                reportId: $reportUuid,
                datasetId: $datasetUuid,
                rlsUser: null,
                rlsRoles: [],
            );
            $accessToken = $embed['token'] ?? null;
        }

        $embedUrl = $embed['embedUrl'] ?? "https://app.powerbi.com/reportEmbed?reportId={$reportUuid}&groupId={$workspaceUuid}";

        $embed = [
            'embedUrl'    => $embedUrl,
            'accessToken' => $accessToken,
            'reportId'    => $reportUuid,
            'workspaceId' => $workspaceUuid,
            'datasetId'   => $datasetUuid,
            'tokenType'   => 'Embed',
        ];

        return view('reports.show', [
            'embed'         => $embed,
            'embedded'      => $request->boolean('embedded', true),
            'report'        => (object) ['id' => $reportUuid, 'name' => $report->name ?? __('reports.show.title_fallback')],
            'workspace'     => (object) ['id' => $workspaceUuid, 'name' => $report->workspace?->name],
            'error'         => null,
            'error_debug'   => null,
        ]);
    }
}
