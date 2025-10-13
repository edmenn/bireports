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
     * Mark activity for the capacity and resume it when rules allow (schedule/hard rules).
     *
     * POST /api/capacities/{capacity}/ping
     * Optional body: { "resume": true } to request a resume when paused.
     */
    public function ping(Request $request, Capacity $capacity): JsonResponse
    {
        $resume = (bool) $request->boolean('resume', true);

        // 1) Record activity immediately
        $this->svc->markActivity($capacity);

        $now = Carbon::now();

        // 1.5) Sync the real state (MyOrg/ARM) to avoid false "paused" readings
        try {
            $active = $this->svc->isCapacityActive($capacity);
            if (is_bool($active) && $capacity->is_active !== $active) {
                $capacity->is_active = $active;
                $capacity->save();
            }
        } catch (\Throwable $e) {
            // If the query fails, keep relying on the local state
        }

        // 2) If paused and resume is requested => follow the rules
        $resumed = false;
        $reason  = null;

        if ($resume && $capacity->isPausable() && ! $capacity->is_active) {
            $inWindow = $this->isInWindowNow($capacity, $now);
            $hasEnabledSchedules = $capacity->schedules()->where('enabled', true)->exists();

            // Does the report allow resuming outside the schedule window?
            $allowOutside = false;
            $reportUuid = (string) $request->input('report_uuid', '');
            if ($reportUuid !== '') {
                try {
                    $report = \App\Models\Report::query()->find($reportUuid);
                    $allowOutside = (bool) ($report?->allow_resume_outside_window ?? false);
                } catch (\Throwable) {}
            }

            // Allowing an outside-window resume requires schedules to be active
            $schedulingActive = $capacity->force_shutdown_on_schedule && $hasEnabledSchedules;
            if ($capacity->force_shutdown_on_schedule && ! $inWindow && ! ($allowOutside && $schedulingActive)) {
                // Blocked outside the window without report override
                $reason = $hasEnabledSchedules ? 'outside_schedule_window' : 'no_schedule_defined';
            } else {
                // Resume inside the window or using the report override
                $this->svc->resume($capacity);
                $capacity->is_active = true;
                $capacity->last_state_change_at = $now;

                // When resumed outside the window, set a grace period
                if (! $inWindow) {
                    $timeout = (int) ($capacity->inactivity_timeout_minutes ?? 0);
                    $graceMin = $timeout > 0 ? $timeout : 30; // default 30 min
                    $capacity->outside_window_grace_until = $now->copy()->addMinutes($graceMin);
                } else {
                    $capacity->outside_window_grace_until = null;
                }

                $capacity->save();
                $resumed = true;
                $reason  = $inWindow ? 'resumed_in_schedule_window' : 'resumed_on_demand_outside_window';
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
     * Determine whether the current time falls within an active window for the capacity.
     */
    private function isInWindowNow(Capacity $cap, Carbon $now): bool
    {
        $weekday = (int) $now->dayOfWeek; // 0=Sun .. 6=Sat

        foreach ($cap->schedules()->where('weekday', $weekday)->where('enabled', true)->get() as $slot) {
            // start_time / end_time arrive as TIME; accessors return Carbon instances for "today"
            $start = $slot->start_time;
            $end   = $slot->end_time;

            if ($start->lessThanOrEqualTo($now) && $now->lessThan($end)) {
                return true;
            }
        }

        return false;
    }
}
