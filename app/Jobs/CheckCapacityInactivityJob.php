<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Capacity;
use App\Services\PowerBiCapacityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CheckCapacityInactivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $capacityId)
    {
        $this->onQueue('capacities');
    }

    public function handle(PowerBiCapacityService $svc): void
    {
        /** @var Capacity|null $cap */
        $cap = Capacity::query()->with('activity', 'schedules')->find($this->capacityId);
        if (! $cap) {
            return;
        }
        if (! $cap->isPausable()) {
            return;
        }
        if (! $cap->cost_optimization_enabled) {
            return;
        }

        $timeout = $cap->inactivity_timeout_minutes;
        if ($timeout === null || $timeout <= 0) {
            return;
        }

        $lastSeen = optional($cap->activity)->last_seen_at;
        $now = Carbon::now();

        // Si está fuera de ventana y se fuerza el apagado por horario, no decidimos aquí.
        if ($cap->force_shutdown_on_schedule && ! $this->isInWindowNow($cap)) {
            return;
        }

        // Si estuvo inactiva más de N minutos y está activa => pausar
        if ($cap->is_active && ($lastSeen === null || $lastSeen->diffInMinutes($now) >= $timeout)) {
            try {
                $svc->pause($cap->capacity_uuid);
                $cap->is_active = false;
                $cap->last_state_change_at = Carbon::now();
                $cap->outside_window_grace_until = null; // limpiar gracia al pausar por inactividad
                $cap->save();
                \App\Models\CapacityAudit::create([
                    'capacity_id' => $cap->id,
                    'action'      => 'inactivity_pause',
                    'result'      => 'success',
                    'message'     => "Paused after {$timeout} min of inactivity",
                ]);
                Log::info("Capacity {$cap->display_name} paused by inactivity ({$timeout} min)");
            } catch (\Throwable $e) {
                \App\Models\CapacityAudit::create([
                    'capacity_id' => $cap->id,
                    'action'      => 'inactivity_pause',
                    'result'      => 'error',
                    'message'     => (string) $e->getMessage(),
                ]);
            }
        }
    }

    private function isInWindowNow(Capacity $cap): bool
    {
        $now = Carbon::now();
        $weekday = (int) $now->dayOfWeek;

        foreach ($cap->schedules->where('weekday', $weekday)->where('enabled', true) as $slot) {
            $start = Carbon::createFromTimeString($slot->start_time->format('H:i:s'));
            $end   = Carbon::createFromTimeString($slot->end_time->format('H:i:s'));
            if ($start->lessThanOrEqualTo($now) && $now->lessThan($end)) {
                return true;
            }
        }
        return false;
    }
}
