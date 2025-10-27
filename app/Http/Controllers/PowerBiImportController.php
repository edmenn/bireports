<?php

namespace App\Http\Controllers;

use App\Services\PowerBiService;
use App\Models\Workspace;
use App\Models\Report;
use App\Models\Dataset;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PowerBiImportController extends Controller
{
    public function importWorkspacesAndReports(PowerBiService $powerBi)
    {
        // 1) Fetch workspaces
        $groups = $powerBi->listWorkspaces();

        $wsCount = 0;
        $rpCount = 0;

        foreach ($groups as $g) {
            // Normalise fields used in the migration
            $workspaceData = [
                'id'           => (string)($g['id'] ?? ''),
                'name'         => (string)($g['name'] ?? __('common.unnamed')),
                'type'         => (string)($g['type'] ?? null),
                'is_read_only' => (bool)  ($g['isReadOnly'] ?? false),
                'capacity_id'  => isset($g['capacityId']) ? (string)$g['capacityId'] : null,
            ];

            if (!$workspaceData['id']) {
                continue; // Skip entries without an id
            }

            Workspace::updateOrCreate(
                ['id' => $workspaceData['id']],
                [
                    'name'         => $workspaceData['name'],
                    'type'         => $workspaceData['type'],
                    'is_read_only' => $workspaceData['is_read_only'],
                    'capacity_id'  => $workspaceData['capacity_id'],
                ]
            );
            $wsCount++;

            // 2) For each workspace, fetch and persist datasets
            $datasets = $powerBi->listDatasets($workspaceData['id']);
            foreach ($datasets as $d) {
                $datasetData = [
                    'id'                   => (string)($d['id'] ?? ''),
                    'name'                 => (string)($d['name'] ?? __('common.unnamed')),
                    'workspace_id'         => $workspaceData['id'],
                    'configured_by'        => isset($d['configuredBy']) ? (string)$d['configuredBy'] : null,
                    'is_refreshable'       => (bool)  ($d['isRefreshable'] ?? false),
                    'add_rows_api_enabled' => (bool)  ($d['addRowsAPIEnabled'] ?? false),
                ];
                if (!$datasetData['id']) { continue; }
                Dataset::updateOrCreate(
                    ['id' => $datasetData['id']],
                    [
                        'name'                  => $datasetData['name'],
                        'workspace_id'          => $datasetData['workspace_id'],
                        'configured_by'         => $datasetData['configured_by'],
                        'is_refreshable'        => $datasetData['is_refreshable'],
                        'add_rows_api_enabled'  => $datasetData['add_rows_api_enabled'],
                    ]
                );
            }

            // 3) For each workspace, fetch and persist reports
            $reports = $powerBi->listReports($workspaceData['id']);
            foreach ($reports as $r) {
                $reportData = [
                    'id'           => (string)($r['id'] ?? ''),
                    'name'         => (string)($r['name'] ?? __('common.unnamed')),
                    'workspace_id' => $workspaceData['id'],
                    'dataset_id'   => isset($r['datasetId']) ? (string)$r['datasetId'] : null,
                    'web_url'      => isset($r['webUrl']) ? (string)$r['webUrl'] : null,
                ];

                if (!$reportData['id']) {
                    continue;
                }

                Report::updateOrCreate(
                    ['id' => $reportData['id']],
                    [
                        'name'         => $reportData['name'],
                        'workspace_id' => $reportData['workspace_id'],
                        'dataset_id'   => $reportData['dataset_id'],
                        'web_url'      => $reportData['web_url'],
                    ]
                );
                $rpCount++;
            }
        }

        // 3b) Import capacities and discover ARM details (subscription/RG and arm_resource_name) automatically
        try {
            Artisan::call('powerbi:capacities:import');
            Artisan::call('powerbi:discover', ['--dry-run' => 0]);
        } catch (\Throwable $e) {
            Log::warning('Auto ARM discovery after import failed', ['error' => $e->getMessage()]);
        }

        // 4) Show a simple summary
        return view('admin.dev.powerbi_import_result', [
            'wsCount' => $wsCount,
            'rpCount' => $rpCount,
            'dsCount' => Dataset::count(),
        ]);
    }

    /** Import or update datasets for every workspace. */
    public function importDatasets(PowerBiService $powerBi)
    {
        $groups = $powerBi->listWorkspaces();
        $dsCount = 0;
        foreach ($groups as $g) {
            $wsId = (string)($g['id'] ?? '');
            if ($wsId === '') continue;

            // Ensure the workspace exists locally even if minimal
            Workspace::firstOrCreate(['id' => $wsId], [
                'name' => (string)($g['name'] ?? 'Unnamed'),
                'type' => (string)($g['type'] ?? null),
                'is_read_only' => (bool)($g['isReadOnly'] ?? false),
                'capacity_id' => isset($g['capacityId']) ? (string)$g['capacityId'] : null,
            ]);

            $datasets = $powerBi->listDatasets($wsId);
            foreach ($datasets as $d) {
                $datasetData = [
                    'id'                   => (string)($d['id'] ?? ''),
                    'name'                 => (string)($d['name'] ?? 'Unnamed'),
                    'workspace_id'         => $wsId,
                    'configured_by'        => isset($d['configuredBy']) ? (string)$d['configuredBy'] : null,
                    'is_refreshable'       => (bool)  ($d['isRefreshable'] ?? false),
                    'add_rows_api_enabled' => (bool)  ($d['addRowsAPIEnabled'] ?? false),
                ];
                if (!$datasetData['id']) continue;

                Dataset::updateOrCreate(
                    ['id' => $datasetData['id']],
                    [
                        'name'                  => $datasetData['name'],
                        'workspace_id'          => $datasetData['workspace_id'],
                        'configured_by'         => $datasetData['configured_by'],
                        'is_refreshable'        => $datasetData['is_refreshable'],
                        'add_rows_api_enabled'  => $datasetData['add_rows_api_enabled'],
                    ]
                );
                $dsCount++;
            }
        }

        return view('admin.dev.powerbi_import_result', [
            'wsCount' => Workspace::count(),
            'rpCount' => \App\Models\Report::count(),
            'dsCount' => $dsCount,
        ]);
    }
}

