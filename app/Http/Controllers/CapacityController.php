<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Capacity;
use App\Services\PowerBiCapacityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CapacityController extends Controller
{
    /**
     * Listado con filtros simples.
     */
    public function index(Request $request): View
    {
        $q     = trim((string) $request->input('q', ''));
        $state = (string) $request->input('state', ''); // active|suspended|all
        $sku   = trim((string) $request->input('sku', ''));

        $caps = Capacity::query()
            ->when($q !== '', fn($qr) => $qr->where(function ($w) use ($q) {
                $w->where('display_name', 'ilike', "%{$q}%")
                  ->orWhere('capacity_uuid', 'ilike', "%{$q}%")
                  ->orWhere('region', 'ilike', "%{$q}%")
                  ->orWhere('sku', 'ilike', "%{$q}%");
            }))
            ->when($state === 'active', fn($qr) => $qr->where('is_active', true))
            ->when($state === 'suspended', fn($qr) => $qr->where('is_active', false))
            ->when($sku !== '', fn($qr) => $qr->where('sku', 'ilike', "%{$sku}%"))
            ->orderBy('display_name')
            ->paginate(20)
            ->appends($request->query());

        return view('capacities.index', [
            'caps'  => $caps,
            'q'     => $q,
            'state' => $state,
            'sku'   => $sku,
        ]);
    }

    /**
     * Re-sincroniza con Power BI (myorg).
     */
    public function refresh(PowerBiCapacityService $svc): RedirectResponse
    {
        try {
            $res = $svc->importCapacities();
            return back()->with('success', "Refrescado: nuevas {$res['created']}, actualizadas {$res['updated']}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al refrescar: ' . $e->getMessage());
        }
    }

    /**
     * Pausar capacidad via ARM.
     */
    public function pause(Capacity $capacity, PowerBiCapacityService $svc): RedirectResponse
{
    try {
        $svc->pause($capacity);  // <— ahora pasa el modelo
        $capacity->is_active = false;
        $capacity->last_state_change_at = now();
        $capacity->save();
        return back()->with('success', "Capacidad '{$capacity->display_name}' pausada.");
    } catch (\Throwable $e) {
        return back()->with('error', "No se pudo pausar: {$e->getMessage()}");
    }
}

    /**
     * Reanudar capacidad via ARM.
     */
    public function resume(Capacity $capacity, PowerBiCapacityService $svc): RedirectResponse
{
    try {
        $svc->resume($capacity); // <— ahora pasa el modelo
        $capacity->is_active = true;
        $capacity->last_state_change_at = now();
        $capacity->save();
        return back()->with('success', "Capacidad '{$capacity->display_name}' reanudada.");
    } catch (\Throwable $e) {
        return back()->with('error', "No se pudo reanudar: {$e->getMessage()}");
    }
}
}
