<?php

namespace App\Http\Controllers;

use App\Services\PowerBiService;
use App\Models\Workspace;
use App\Models\Report;
use Illuminate\Http\Request;

class PowerBiImportController extends Controller
{
    public function importWorkspacesAndReports(PowerBiService $powerBi)
    {
        // 1) Traer workspaces
        $groups = $powerBi->listWorkspaces();

        $wsCount = 0;
        $rpCount = 0;

        foreach ($groups as $g) {
            // Normalizar campos que usamos en tu migración
            $workspaceData = [
                'id'           => (string)($g['id'] ?? ''),
                'name'         => (string)($g['name'] ?? '(sin nombre)'),
                'type'         => (string)($g['type'] ?? null),
                'is_read_only' => (bool)  ($g['isReadOnly'] ?? false),
                'capacity_id'  => isset($g['capacityId']) ? (string)$g['capacityId'] : null,
            ];

            if (!$workspaceData['id']) {
                continue; // sin ID no guardamos
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

            // 2) Por cada workspace, traer reports y guardarlos
            $reports = $powerBi->listReports($workspaceData['id']);
            foreach ($reports as $r) {
                $reportData = [
                    'id'           => (string)($r['id'] ?? ''),
                    'name'         => (string)($r['name'] ?? '(sin nombre)'),
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

        // 3) Mostrar un resumen simple
        return view('admin.dev.powerbi_import_result', [
            'wsCount' => $wsCount,
            'rpCount' => $rpCount,
        ]);
    }
}
