<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Capacity;
use App\Services\PowerBiCapacityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CapacityActivityController extends Controller
{
    public function __construct(private readonly PowerBiCapacityService $svc)
    {
    }

    /**
     * Marca actividad para la capacidad y, si está pausada, intenta reanudar
     * cuando corresponde (horario/hard rules).
     *
     * POST /api/capacities/{capacity}/ping
     * Body opcional: { "resume": true } para intentar reanudar si estuviera pausada.
     */
    public function ping(Request $request, Capacity $capacity): JsonResponse
    {
        $resume = (bool) $request->boolean('resume', true);

        // 1) Registrar actividad inmediata
        $this->svc->markActivity($capacity);

        $now = Carbon::now();

        // 2) Si está pausada y se solicita resume => decidir según reglas
        $resumed = false;
        $reason  = null;

        if ($resume && $capacity->isPausable() && ! $capacity->is_active) {
            $inWindow = $this->isInWindowNow($capacity, $now);

            if ($capacity->force_shutdown_on_schedule) {
                // Si forzamos apagado por horario: solo reanudamos si estamos dentro de la ventana
                if ($inWindow) {
                    $this->svc->resume($capacity->capacity_uuid);
                    $capacity->is_active = true;
                    $capacity->last_state_change_at = $now;
                    $capacity->save();
                    $resumed = true;
                    $reason  = 'resumed_in_schedule_window';
                } else {
                    $reason = 'outside_schedule_window';
                }
            } else {
                // Si no se fuerza por horario, reanudamos siempre al primer acceso
                $this->svc->resume($capacity->capacity_uuid);
                $capacity->is_active = true;
                $capacity->last_state_change_at = $now;
                $capacity->save();
                $resumed = true;
                $reason  = 'resumed_on_demand';
            }
        }

        return response()->json([
            'ok'       => true,
            'capacity' => [
                'id'                         => $capacity->id,
                'uuid'                       => $capacity->capacity_uuid,
                'display_name'               => $capacity->display_name,
                'is_active'                  => (bool) $capacity->is_active,
                'supports_pause'             => (bool) $capacity->supports_pause,
                'force_shutdown_on_schedule' => (bool) $capacity->force_shutdown_on_schedule,
                'inactivity_timeout_minutes' => $capacity->inactivity_timeout_minutes,
                'last_state_change_at'       => optional($capacity->last_state_change_at)?->toIso8601String(),
            ],
            'resumed'  => $resumed,
            'reason'   => $reason,
        ]);
    }

    /**
     * Determina si ahora mismo estamos dentro de algún intervalo activo para esta capacidad.
     */
    private function isInWindowNow(Capacity $cap, Carbon $now): bool
    {
        $weekday = (int) $now->dayOfWeek; // 0=Dom .. 6=Sab

        foreach ($cap->schedules()->where('weekday', $weekday)->where('enabled', true)->get() as $slot) {
            // start_time / end_time llegan como TIME; en el modelo los accessors devuelven Carbon "de hoy"
            $start = $slot->start_time; // Carbon creado por accessor
            $end   = $slot->end_time;   // Carbon creado por accessor

            if ($start->lessThanOrEqualTo($now) && $now->lessThan($end)) {
                return true;
            }
        }

        return false;
    }
}
