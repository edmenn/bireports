<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PowerBiService; // Usa tu servicio existente para obtener token AAD
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ReportViewerController extends Controller
{
    public function __construct(private readonly PowerBiService $pbi)
    {
        // Requiere auth por rutas (ver web.php)
    }

    /**
     * Renderiza la vista con el embed y variables necesarias para el JS.
     * Espera: report_uuid en la ruta, workspace_uuid (query), dataset_uuid (query opcional).
     */
    public function show(Request $request, string $report_uuid): View
    {
        $v = Validator::make(
            array_merge($request->all(), ['report_uuid' => $report_uuid]),
            [
                'report_uuid'    => 'required|uuid',
                'workspace_uuid' => 'required|uuid',  // necesitamos groupId para el embed
                'dataset_uuid'   => 'nullable|uuid',
                'rls_role'       => 'nullable|string|max:128',
            ]
        )->validate();

        $reportUuid    = (string) $v['report_uuid'];
        $workspaceUuid = (string) $v['workspace_uuid'];
        $datasetUuid   = $v['dataset_uuid'] ?? null;
        $rlsRole       = $v['rls_role'] ?? null;

        // Si no hay configuración completa, devolvemos vista con datos vacíos y sin error 500
        $hasConfig = Configuracion::query()
            ->whereNotNull('powerbi_tenant_id')->where('powerbi_tenant_id', '!=', '')
            ->whereNotNull('powerbi_client_id')->where('powerbi_client_id', '!=', '')
            ->whereNotNull('powerbi_client_secret')->where('powerbi_client_secret', '!=', '')
            ->exists();

        if (! $hasConfig) {
            $accessToken = null;
        } else {
            // Token AAD (Service Principal) con getAccessToken() de tu PowerBiService
            $accessToken = $this->pbi->getAccessToken();
        }

        // Embed URL estándar para reportes
        $embedUrl = "https://app.powerbi.com/reportEmbed?reportId={$reportUuid}&groupId={$workspaceUuid}";

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
            'report'        => (object) ['id' => $reportUuid, 'name' => 'Reporte'],
            'workspace'     => (object) ['id' => $workspaceUuid, 'name' => null],
            'error'         => null,
            'error_debug'   => null,
        ]);
    }
}
