<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Services\PowerBiService;
use Illuminate\Http\RedirectResponse;

class PowerBiImportController extends Controller
{
    /**
     * Importa/actualiza Workspaces desde la API y guarda en BD.
     * - Por ahora dejamos esto accesible solo para super_admin.
     */
    public function importWorkspaces(PowerBiService $pbi): RedirectResponse
    {
        $items = $pbi->listWorkspaces();

        foreach ($items as $g) {
            // Campos típicos que devuelve la API:
            // id, name, capacityId, isOnDedicatedCapacity, isOnPremiumPerUserWorkspace
            Workspace::updateOrCreate(
                ['id' => (string) ($g['id'] ?? '')],
                [
                    'name'                     => (string) ($g['name'] ?? '(sin nombre)'),
                    'capacity_id'              => $g['capacityId']            ?? null,
                    'is_on_dedicated_capacity' => (bool)   ($g['isOnDedicatedCapacity']        ?? false),
                    'is_on_premium_per_user'   => (bool)   ($g['isOnPremiumPerUserWorkspace']   ?? false),
                    // si luego querés asociar al cliente logueado:
                    // 'client_id' => auth()->user()->client_id ?? null,
                ]
            );
        }

        return redirect()
            ->route('workspaces.index')
            ->with('status', 'Workspaces importados/actualizados correctamente.');
    }
}
