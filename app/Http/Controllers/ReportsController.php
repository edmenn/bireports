<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\PowerBiService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    /**
     * Listado global de todos los reportes importados (con paginación).
     */
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $reports = Report::query()
            ->with('workspace')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'ilike', "%{$q}%")
                      ->orWhere('id', 'ilike', "%{$q}%")
                      ->orWhere('workspace_id', 'ilike', "%{$q}%")
                      ->orWhere('dataset_id', 'ilike', "%{$q}%");
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
     * Ver un reporte embebido.
     */
    public function show(string $workspaceId, string $reportId, PowerBiService $svc, Request $request): View
    {
        // opcionales por querystring para testear RLS: ?rls_user=...&rls_roles=RoleA,RoleB
        $rlsUser  = $request->query('rls_user');
        $rlsRoles = array_filter(array_map('trim', explode(',', (string)$request->query('rls_roles', ''))));

        $report = Report::where('id', $reportId)
            ->where('workspace_id', $workspaceId)
            ->firstOrFail();

        $embed = $svc->generateReportEmbedToken(
            workspaceId: $workspaceId,
            reportId:    $reportId,
            datasetId:   $report->dataset_id, // si está persistido lo usamos
            rlsUser:     $rlsUser ?: null,
            rlsRoles:    $rlsRoles
        );

        return view('reports.show', [
            'report' => $report,
            'embed'  => $embed,
        ]);
    }
}
