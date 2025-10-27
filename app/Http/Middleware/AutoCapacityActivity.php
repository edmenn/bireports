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
     * Applies on-demand activity tracking and auto-resume when appropriate.
     *
     * Activation rules:
     * - Paths that start with /embed (GET/HEAD), or
     * - Requests that include capacity_id (query ?capacity_id= or X-Capacity-Id header)
     */
    public function handle(Request $request, Closure $next)
    {
        // Should this request trigger the middleware?
        $path = $request->path(); // without the leading slash
        $hasCapacityParam = $request->query('capacity_id') !== null || $request->headers->has('X-Capacity-Id');
        $looksLikeEmbed   = str_starts_with('/' . ltrim($path, '/'), '/embed');

        if (! $looksLikeEmbed && ! $hasCapacityParam) {
            return $next($request);
        }

        // Resolve capacity_id (priority: query -> header)
        $capacityId = (string) ($request->query('capacity_id') ?? $request->headers->get('X-Capacity-Id', ''));

        /** @var Capacity|null $capacity */
        $capacity = null;

        if ($capacityId !== '') {
            // Accepts numeric id (PK) or UUID; try PK first
            $capacity = is_numeric($capacityId)
                ? Capacity::query()->with('schedules', 'activity')->find((int) $capacityId)
                : Capacity::query()->with('schedules', 'activity')->where('capacity_uuid', $capacityId)->first();
        }

        // If capacity_id is missing for /embed/* requests, provide your own mapping:
        // (We do not assume any mapping here; build a workspace/report -> capacity table if required.)
        if ($capacity === null && $looksLikeEmbed) {
            return $next($request);
        }

        // Always register activity
        $this->svc->markActivity($capacity);

        // Attempt to resume when paused and eligible
        if ($capacity->isPausable() && ! $capacity->is_active) {
            $now = Carbon::now();
            $inWindow = $this->isInWindowNow($capacity, $now);

            if ($capacity->force_shutdown_on_schedule) {
                if ($inWindow) {
                    $this->resumeCapacity($capacity, $now);
                }
            } else {
                // No forced schedule => resume on-demand
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
            // The start_time/end_time accessors return Carbon instances set to "today"
            $start = $slot->start_time;
            $end   = $slot->end_time;
            if ($start->lessThanOrEqualTo($now) && $now->lessThan($end)) {
                return true;
            }
        }
        return false;
    }
}
