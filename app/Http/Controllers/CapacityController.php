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
    public function __construct(private readonly PowerBiCapacityService $svc)
    {
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $caps = Capacity::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('display_name', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%")
                      ->orWhere('type', 'like', "%{$q}%");
            })
            ->orderBy('display_name')
            ->with('schedules')
            ->paginate(20)
            ->withQueryString();

        return view('capacities.index', ['caps' => $caps, 'q' => $q]);
    }

    public function toggle(Request $request, Capacity $capacity): RedirectResponse
    {
        $request->validate(['_token' => ['required']]);

        if (! $capacity->isPausable()) {
            return back()->with('error', 'Esta capacidad no soporta pausa/reanudación.');
        }

        try {
            if ($capacity->is_active) {
                $this->svc->pause($capacity->capacity_uuid);
                $capacity->is_active = false;
            } else {
                $this->svc->resume($capacity->capacity_uuid);
                $capacity->is_active = true;
            }

            $capacity->last_state_change_at = now();
            $capacity->save();

            return back()->with('success', 'Acción ejecutada correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudo completar la acción: ' . $e->getMessage());
        }
    }

    public function refresh(): RedirectResponse
    {
        try {
            $res = $this->svc->importCapacities();
            return back()->with(
                'success',
                "Capacidades sincronizadas. Nuevas: {$res['created']} | Actualizadas: {$res['updated']}"
            );
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudo sincronizar: ' . $e->getMessage());
        }
    }
}
