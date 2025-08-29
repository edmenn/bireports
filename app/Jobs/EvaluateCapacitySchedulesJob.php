<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Capacity;
use App\Models\CapacitySchedule;
use App\Services\PowerBiCapacityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class EvaluateCapacitySchedulesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $capacityId)
    {
        $this->onQueue('capacities');
    }

    public function handle(PowerBiCapacityService $svc): void
    {
        /** @var Capacity|null $cap */
        $cap = Capacity::query()->with('schedules')->find($this->capacityId);
        if (! $cap) {
            return;
        }
        if (! $cap->isPausable()) {
            return;
        }

        $now = Carbon::now();
        $weekday = (int) $now->dayOfWeek; // 0=Dom..6=Sab

        /** @var CapacitySchedule[] $today */
        $today = $cap->schedules->where('weekday', $weekday)->where('enabled', true)->all();

        $inWindow = false;
        foreach ($today as $slot) {
            $start = Carbon::createFromTimeString($slot->start_time->format('H:i:s'));
            $end   = Carbon::createFromTimeString($slot->end_time->format('H:i:s'));

            if ($start->lessThanOrEqualTo($now) && $now->lessThan($end)) {
                $inWindow = true;
                break;
            }
        }

        if ($cap->force_shutdown_on_schedule) {
            if ($inWindow && ! $cap->is_active) {
                $svc->resume($cap->capacity_uuid);
                $cap->is_active = true;
                $cap->last_state_change_at = Carbon::now();
                $cap->save();
                Log::info("Capacity {$cap->display_name} resumed by schedule");
            } elseif (! $inWindow && $cap->is_active) {
                $svc->pause($cap->capacity_uuid);
                $cap->is_active = false;
                $cap->last_state_change_at = Carbon::now();
                $cap->save();
                Log::info("Capacity {$cap->display_name} paused by schedule");
            }
        }
    }
}
