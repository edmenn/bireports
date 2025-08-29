<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Capacity;
use App\Services\PowerBiCapacityService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AutoCapacityActivity
{
    public function __construct(private readonly PowerBiCapacityService $svc)
    {
    }

    /**
     * Aplica actividad on-demand y auto-resume si corresponde.
     *
     * Reglas de activación:
     * - Path que comience con /embed (GET/HEAD), o
     * - Request con capacity_id (query ?capacity_id= o header X-Capacity-Id)
     */
    public function handle(Request $request, Closure $next)
    {
        // ¿Debemos actuar en esta request?
        $path = $request->path(); // sin slash inicial
        $hasCapacityParam = $request->query('capacity_id') !== null || $request->headers->has('X-Capacity-Id');
        $looksLikeEmbed   = str_starts_with('/' . ltrim($path, '/'), '/embed');

        if (! $looksLikeEmbed && ! $hasCapacityParam) {
            return $next($request);
        }

        // Resolver capacity_id (prioridad: query -> header)
        $capacityId = (string) ($request->query('capacity_id') ?? $request->headers->get('X-Capacity-Id', ''));

        /** @var Capacity|null $capacity */
        $capacity = null;

        if ($capacityId !== '') {
            // Acepta ID numérico (PK) o UUID; primero intentamos por PK
            $capacity = is_numeric($capacityId)
                ? Capacity::query()->with('schedules', 'activity')->find((int) $capacityId)
                : Capacity::query()->with('schedules', 'activity')->where('capacity_uuid', $capacityId)->first();
        }

        // Si no vino capacity_id pero el path es /embed/*, podés mapear por tu lógica:
        // (Aquí no asumimos un mapa; si lo necesitás, en un paso aparte agregamos tabla workspace/report -> capacity)
        if ($capacity === null && $looksLikeEmbed) {
            return $next($request);
        }

        // Marcar actividad siempre
        $this->svc->markActivity($capacity);

        // Intentar reanudar si está pausada y es pausable
        if ($capacity->isPausable() && ! $capacity->is_active) {
            $now = Carbon::now();
            $inWindow = $this->isInWindowNow($capacity, $now);

            if ($capacity->force_shutdown_on_schedule) {
                if ($inWindow) {
                    $this->resumeCapacity($capacity, $now);
                }
            } else {
                // Sin forzar por horario => reanudar on-demand
                $this->resumeCapacity($capacity, $now);
            }
        }

        return $next($request);
    }

    private function resumeCapacity(Capacity $capacity, Carbon $when): void
    {
        $this->svc->resume($capacity->capacity_uuid);
        $capacity->is_active = true;
        $capacity->last_state_change_at = $when;
        $capacity->save();
    }

    private function isInWindowNow(Capacity $cap, Carbon $now): bool
    {
        $weekday = (int) $now->dayOfWeek; // 0..6
        foreach ($cap->schedules()->where('weekday', $weekday)->where('enabled', true)->get() as $slot) {
            // En tus modelos, los accessors de start_time/end_time devuelven Carbon "de hoy"
            $start = $slot->start_time;
            $end   = $slot->end_time;
            if ($start->lessThanOrEqualTo($now) && $now->lessThan($end)) {
                return true;
            }
        }
        return false;
    }
}
