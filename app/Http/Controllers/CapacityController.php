<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Capacity;
use App\Services\PowerBiCapacityService;
use App\Models\Configuration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CapacityController extends Controller
{
    /**
     * Lists capacities with throttled auto-sync behaviour.
     * - Sync occurs at most once every $throttleSeconds.
     * - Supports basic filters: q | state(active|paused|all) | sku
     */
    public function index(Request $request): View
    {
        // ⏱️ Adjust this value to widen or shrink the sync window
        $throttleSeconds = 120;

        // 1) Attempt a throttled sync when configuration is available
        $hasConfig = Configuration::query()
            ->whereNotNull('powerbi_tenant_id')->where('powerbi_tenant_id', '!=', '')
            ->whereNotNull('powerbi_client_id')->where('powerbi_client_id', '!=', '')
            ->whereNotNull('powerbi_client_secret')->where('powerbi_client_secret', '!=', '')
            ->exists();
        if ($hasConfig) {
            $svc = app(PowerBiCapacityService::class);
            $this->syncWithThrottle($svc, $throttleSeconds);
        }

        // 2) Filters
        $q     = trim((string) $request->input('q', ''));
        $state = (string) $request->input('state', 'all'); // all|active|paused
        $sku   = trim((string) $request->input('sku', ''));

        // 3) Build the filtered query
        $caps = Capacity::query()
            ->when($q !== '', function ($qr) use ($q) {
                $needle = mb_strtolower($q);
                $like = '%' . $needle . '%';
                $qr->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(display_name) LIKE ?', [$like])
                      ->orWhereRaw('LOWER(capacity_uuid) LIKE ?', [$like])
                      ->orWhereRaw('LOWER(region) LIKE ?', [$like])
                      ->orWhereRaw('LOWER(sku) LIKE ?', [$like]);
                });
            })
            ->when($state === 'active', fn ($qr) => $qr->where('is_active', true))
            ->when($state === 'paused', fn ($qr) => $qr->where('is_active', false))
            ->when($sku !== '', function ($qr) use ($sku) {
                $likeSku = '%' . mb_strtolower($sku) . '%';
                return $qr->whereRaw('LOWER(sku) LIKE ?', [$likeSku]);
            })
            ->orderBy('display_name')
            ->paginate(20)
            ->appends($request->query());

        return view('capacities.index', [
            'caps'  => $caps,
            'q'     => $q,
            'state' => $state,
            'sku'   => $sku,
            'hasConfig' => $hasConfig,
        ]);
    }

    /**
     * Manually synchronises capacities with Power BI.
     * Useful when exposing a "Refresh" action in the UI.
     */
    public function refresh(): RedirectResponse
    {
        $hasConfig = Configuration::query()
            ->whereNotNull('powerbi_tenant_id')->where('powerbi_tenant_id', '!=', '')
            ->whereNotNull('powerbi_client_id')->where('powerbi_client_id', '!=', '')
            ->whereNotNull('powerbi_client_secret')->where('powerbi_client_secret', '!=', '')
            ->exists();
        if (! $hasConfig) {
            return back()->with('error', __('capacities.messages.missing_config'));
        }
        try {
            $svc = app(PowerBiCapacityService::class);
            $res = $svc->importCapacities();
            // Reset the last sync timestamp so future auto-syncs are not blocked
            Cache::forget('capacities:last_sync_at');

            return back()->with(
                'success',
                __('capacities.messages.refresh_success', [
                    'created' => $res['created'],
                    'updated' => $res['updated'],
                ])
            );
        } catch (\Throwable $e) {
            Log::error('Capacities manual refresh error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', __('capacities.messages.refresh_error', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Pause a capacity through ARM.
     */
    public function pause(Capacity $capacity): RedirectResponse
    {
        try {
            $hasConfig = Configuration::query()
                ->whereNotNull('powerbi_tenant_id')->where('powerbi_tenant_id', '!=', '')
                ->whereNotNull('powerbi_client_id')->where('powerbi_client_id', '!=', '')
                ->whereNotNull('powerbi_client_secret')->where('powerbi_client_secret', '!=', '')
                ->exists();
            if (! $hasConfig) {
                return back()->with('error', __('capacities.messages.pause_missing_config'));
            }
            $svc = app(PowerBiCapacityService::class);
            $svc->pause($capacity);
            $capacity->is_active = false;
            $capacity->last_state_change_at = now();
            $capacity->save();
            \App\Models\CapacityAudit::create([
                'capacity_id' => $capacity->id,
                'action'      => 'pause',
                'result'      => 'success',
                'message'     => __('capacities.audit.log.paused_by_user'),
            ]);

            // Clear the marker when the state changes to allow a fresh sync
            Cache::forget('capacities:last_sync_at');

            return back()->with('success', __('capacities.messages.pause_success', ['name' => $capacity->display_name]));
        } catch (\Throwable $e) {
            Log::error('Pause capacity error: '.$e->getMessage(), [
                'capacity_id' => $capacity->id,
                'exception'   => $e,
            ]);
            \App\Models\CapacityAudit::create([
                'capacity_id' => $capacity->id,
                'action'      => 'pause',
                'result'      => 'error',
                'message'     => (string) $e->getMessage(),
            ]);
            return back()->with('error', __('capacities.messages.pause_error', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Resume a capacity through ARM.
     */
    public function resume(Capacity $capacity): RedirectResponse
    {
        try {
            $hasConfig = Configuration::query()
                ->whereNotNull('powerbi_tenant_id')->where('powerbi_tenant_id', '!=', '')
                ->whereNotNull('powerbi_client_id')->where('powerbi_client_id', '!=', '')
                ->whereNotNull('powerbi_client_secret')->where('powerbi_client_secret', '!=', '')
                ->exists();
            if (! $hasConfig) {
                return back()->with('error', __('capacities.messages.resume_missing_config'));
            }
            $svc = app(PowerBiCapacityService::class);
            $svc->resume($capacity);
            $capacity->is_active = true;
            $capacity->last_state_change_at = now();
            $capacity->save();
            \App\Models\CapacityAudit::create([
                'capacity_id' => $capacity->id,
                'action'      => 'resume',
                'result'      => 'success',
                'message'     => __('capacities.audit.log.resumed_by_user'),
            ]);

            Cache::forget('capacities:last_sync_at');

            return back()->with('success', __('capacities.messages.resume_success', ['name' => $capacity->display_name]));
        } catch (\Throwable $e) {
            Log::error('Resume capacity error: '.$e->getMessage(), [
                'capacity_id' => $capacity->id,
                'exception'   => $e,
            ]);
            \App\Models\CapacityAudit::create([
                'capacity_id' => $capacity->id,
                'action'      => 'resume',
                'result'      => 'error',
                'message'     => (string) $e->getMessage(),
            ]);
            return back()->with('error', __('capacities.messages.resume_error', ['message' => $e->getMessage()]));
        }
    }

    // ===== Scheduling (start/pause) and inactivity rules =====
    public function schedule(Capacity $capacity): View
    {
        $capacity->load('schedules');
        $dayNames = [
            __('common.days.sunday'),
            __('common.days.monday'),
            __('common.days.tuesday'),
            __('common.days.wednesday'),
            __('common.days.thursday'),
            __('common.days.friday'),
            __('common.days.saturday'),
        ];
        return view('capacities.schedule', [
            'capacity'  => $capacity,
            'schedules' => $capacity->schedules()->orderBy('weekday')->orderBy('start_time')->get(),
            'dayNames'  => $dayNames,
        ]);
    }

    public function scheduleStore(Request $request, Capacity $capacity): RedirectResponse
    {
        $normalizedStart = null;
        $normalizedEnd   = null;

        $validator = Validator::make($request->all(), [
            'days'       => ['required','array','min:1'],
            'days.*'     => ['integer','between:0,6'],
            'start_time' => ['required'],
            'end_time'   => ['required'],
        ]);

        $validator->after(function ($validator) use ($request, &$normalizedStart, &$normalizedEnd) {
            $start = $this->parseScheduleTime($request->input('start_time'));
            if (! $start) {
                $validator->errors()->add('start_time', __('capacities.schedule.validation.invalid_time'));
            }

            $end = $this->parseScheduleTime($request->input('end_time'));
            if (! $end) {
                $validator->errors()->add('end_time', __('capacities.schedule.validation.invalid_time'));
            }

            if ($start && $end && $end['seconds'] <= $start['seconds']) {
                $validator->errors()->add('end_time', __('capacities.schedule.validation.end_before_start'));
            }

            if ($start) {
                $normalizedStart = $start['time'];
            }
            if ($end) {
                $normalizedEnd = $end['time'];
            }
        });

        $validated = $validator->validate();

        foreach (array_unique($validated['days']) as $wd) {
            $capacity->schedules()->create([
                'weekday'    => (int)$wd,
                'start_time' => $normalizedStart,
                'end_time'   => $normalizedEnd,
                'enabled'    => true,
            ]);
        }
        return back()->with('success', __('capacities.messages.schedule_added'));
    }

    public function scheduleToggle(Capacity $capacity, int $slot): RedirectResponse
    {
        $slot = \App\Models\CapacitySchedule::query()->where('capacity_id',$capacity->id)->where('id',$slot)->firstOrFail();
        $slot->enabled = ! $slot->enabled;
        $slot->save();
        return back()->with('success', $slot->enabled ? __('capacities.messages.schedule_enabled') : __('capacities.messages.schedule_disabled'));
    }

    public function scheduleDelete(Capacity $capacity, int $slot): RedirectResponse
    {
        \App\Models\CapacitySchedule::query()->where('capacity_id',$capacity->id)->where('id',$slot)->delete();
        return back()->with('success', __('capacities.messages.schedule_deleted'));
    }

    public function settingsUpdate(Request $request, Capacity $capacity): RedirectResponse
    {
        $validated = $request->validate([
            'automation_enabled'          => ['nullable','boolean'],
            'inactivity_timeout_minutes'  => ['nullable','integer','min:5','max:120'],
        ]);

        // Unified switch: force both flags simultaneously
        $auto = $request->boolean('automation_enabled');
        $capacity->force_shutdown_on_schedule = $auto;
        $capacity->cost_optimization_enabled  = $auto;
        $capacity->inactivity_timeout_minutes = $validated['inactivity_timeout_minutes'] ?? null;

        // When automation is enabled without a timeout, default to 30 minutes
        if ($auto && empty($capacity->inactivity_timeout_minutes)) {
            $capacity->inactivity_timeout_minutes = 30;
        }
        $capacity->save();

        return back()->with('success', __('capacities.messages.settings_saved'));
    }

    public function audit(Capacity $capacity): View
    {
        // 1) Local logs (database)
        $localRaw = \App\Models\CapacityAudit::query()->where('capacity_id',$capacity->id)
            ->orderByDesc('id')->limit(200)->get();

        // Normalize local entries to simple objects with a source field
        $local = collect();
        foreach ($localRaw as $l) {
            $row = new class($l) {
                public $created_at; public $action; public $result; public $message; public $source;
                public function __construct($m){
                    $this->created_at = $m->created_at; $this->action = $m->action; $this->result = $m->result; $this->message = $m->message; $this->source = 'local';
                }
            };
            $local->push($row);
        }

        // 2) API logs (Azure Activity Logs / ARM)
        $apiItems = collect();
        try {
            /** @var PowerBiCapacityService $svc */
            $svc = app(PowerBiCapacityService::class);
            $events = $svc->getCapacityActivityLogs($capacity, now()->subDays(14), now(), 200);

            foreach ($events as $ev) {
                $ts = isset($ev['eventTimestamp']) ? \Illuminate\Support\Carbon::parse((string) $ev['eventTimestamp']) : now();
                $op = (string)($ev['operationName']['value'] ?? '');
                $status = strtoupper((string)($ev['status']['value'] ?? ($ev['status'] ?? '')));
                $caller = (string)($ev['caller'] ?? '');
                $corrId = (string)($ev['correlationId'] ?? '');

                $action = str_contains(strtolower($op), 'resume') ? 'resume' : (str_contains(strtolower($op), 'suspend') ? 'pause' : 'other');
                $result = $status === 'SUCCEEDED' ? 'success' : ($status === 'FAILED' ? 'error' : strtolower($status ?: '-'));
                $msgParts = [];
                $msgParts[] = 'API';
                if ($caller !== '') { $msgParts[] = "by $caller"; }
                if ($corrId !== '') { $msgParts[] = "corrId=$corrId"; }
                $message = implode(' ', $msgParts);

                // Create objects compatible with the view (properties aligned with the model)
                $row = new class($ts, $action, $result, $message) {
                    public $created_at; public $action; public $result; public $message; public $source;
                    public function __construct($created_at, $action, $result, $message){
                        $this->created_at = $created_at; $this->action = $action; $this->result = $result; $this->message = $message; $this->source = 'api';
                    }
                };
                $apiItems->push($row);
            }
        } catch (\Throwable $e) {
            // If it fails (missing ARM credentials, etc.), continue with local data only
        }

        // 3) Merge and sort descending by date
        $logs = $apiItems
            ->merge($local)
            ->sortByDesc(function ($x) { return optional($x->created_at)->getTimestamp() ?? 0; })
            ->take(200);

        return view('capacities.audit', compact('capacity','logs'));
    }

    /**
     * Refresh capacity metadata from Power BI and redirect back to the audit page.
     */
    public function auditRefresh(Capacity $capacity): \Illuminate\Http\RedirectResponse
    {
        $hasConfig = \App\Models\Configuration::query()
            ->whereNotNull('powerbi_tenant_id')->where('powerbi_tenant_id', '!=', '')
            ->whereNotNull('powerbi_client_id')->where('powerbi_client_id', '!=', '')
            ->whereNotNull('powerbi_client_secret')->where('powerbi_client_secret', '!=', '')
            ->exists();
        if (! $hasConfig) {
            return back()->with('error', __('capacities.messages.missing_config'));
        }

        try {
            /** @var PowerBiCapacityService $svc */
            $svc = app(PowerBiCapacityService::class);
            $r   = $svc->getCapacity($capacity->capacity_uuid);

            if (empty($r)) {
                return back()->with('error', __('capacities.messages.metadata_fetch_error'));
            }

            $display = (string) ($r['displayName'] ?? $r['name'] ?? $capacity->display_name);
            $sku     = (string) ($r['sku'] ?? ($r['administration']['sku'] ?? ($capacity->sku ?? '')));
            $region  = (string) ($r['region'] ?? ($capacity->region ?? ''));
            $state   = (string) ($r['state'] ?? '');
            $type    = (string) ($r['type'] ?? ($capacity->type ?? ''));

            $supportsPause = ! str_contains(strtoupper($sku), 'PPU');

            $capacity->fill([
                'display_name'   => $display,
                'sku'            => $sku,
                'type'           => $type,
                'region'         => $region,
                'supports_pause' => $supportsPause,
                'is_active'      => $state !== '' ? strtoupper($state) === 'ACTIVE' : $capacity->is_active,
            ])->save();

            return redirect()->route('capacities.audit', $capacity)->with('success', __('capacities.messages.metadata_updated'));
        } catch (\Throwable $e) {
            return back()->with('error', __('capacities.messages.metadata_update_error', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Syncs against Fabric/Power BI when:
     *   - The last sync is older than $windowSeconds
     *   - And a lock is acquired to prevent concurrency
     *
     * Guarantees:
     *   - Failures are not cached: the timestamp is set only on success.
     *   - The UI keeps working even if imports fail.
     */
    private function syncWithThrottle(PowerBiCapacityService $svc, int $windowSeconds = 120): void
    {
        try {
            $last = Cache::get('capacities:last_sync_at');
            if ($last && now()->diffInSeconds($last) < $windowSeconds) {
                // Inside the time window: skip sync
                return;
            }

            // Prevent concurrent sync attempts (lock expires in windowSeconds)
            $lock = Cache::lock('capacities:syncing', $windowSeconds);

            if ($lock->get()) {
                try {
                    $svc->importCapacities(); // Actual sync against Fabric/Power BI
                    // Record the last sync only when the import succeeded
                    Cache::put('capacities:last_sync_at', now(), $windowSeconds);
                } finally {
                    optional($lock)->release();
                }
            } else {
                // Another process is already syncing; do nothing.
                return;
            }
        } catch (\Throwable $e) {
            Log::warning('Capacities auto-sync failed: '.$e->getMessage(), ['exception' => $e]);
            // Do not interrupt the view when the sync fails.
        }
    }

    /**
     * Normalise time inputs coming from browsers (24h or 12h with AM/PM).
     */
    private function parseScheduleTime($value): ?array
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        $normalized = str_ireplace(
            ['a. m.', 'p. m.', 'am.', 'pm.', 'a . m .', 'p . m .'],
            ['am', 'pm', 'am', 'pm', 'am', 'pm'],
            $normalized
        );
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = $normalized !== null ? trim($normalized) : '';
        if ($normalized === '') {
            return null;
        }

        $formats = [
            'H:i',
            'H:i:s',
            'G:i',
            'G:i:s',
            'g:i A',
            'g:i a',
            'g:iA',
            'g:ia',
            'g:i:s A',
            'g:i:s a',
            'h:i A',
            'h:i a',
            'h:iA',
            'h:ia',
            'h:i:s A',
            'h:i:s a',
        ];

        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat('!' . $format, $normalized);
            if ($dt !== false) {
                $hour    = (int) $dt->format('H');
                $minute  = (int) $dt->format('i');
                $second  = (int) $dt->format('s');
                $seconds = ($hour * 3600) + ($minute * 60) + $second;

                return [
                    'time'    => $dt->format('H:i:s'),
                    'seconds' => $seconds,
                ];
            }
        }

        return null;
    }
}
