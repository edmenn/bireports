<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Capacity;
use App\Services\PowerBiCapacityService;
use App\Models\Configuracion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CapacityController extends Controller
{
    /**
     * Listado de capacidades con auto-sync throttled.
     * - Sincroniza como máximo una vez cada $throttleSeconds.
     * - Aplica filtros básicos: q | state(active|paused|all) | sku
     */
    public function index(Request $request): View
    {
        // ⏱️ Cambiá este valor para ampliar o reducir la ventana de sincronización
        $throttleSeconds = 120;

        // 1) Intento de sincronización con limitación (solo si hay configuración)
        $hasConfig = Configuracion::query()
            ->whereNotNull('powerbi_tenant_id')->where('powerbi_tenant_id', '!=', '')
            ->whereNotNull('powerbi_client_id')->where('powerbi_client_id', '!=', '')
            ->whereNotNull('powerbi_client_secret')->where('powerbi_client_secret', '!=', '')
            ->exists();
        if ($hasConfig) {
            $svc = app(PowerBiCapacityService::class);
            $this->syncWithThrottle($svc, $throttleSeconds);
        }

        // 2) Filtros
        $q     = trim((string) $request->input('q', ''));
        $state = (string) $request->input('state', 'all'); // all|active|paused
        $sku   = trim((string) $request->input('sku', ''));

        // 3) Query con filtros
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
     * Re-sincroniza manualmente con Power BI.
     * Útil si mantenés el botón "Refrescar".
     */
    public function refresh(): RedirectResponse
    {
        $hasConfig = Configuracion::query()
            ->whereNotNull('powerbi_tenant_id')->where('powerbi_tenant_id', '!=', '')
            ->whereNotNull('powerbi_client_id')->where('powerbi_client_id', '!=', '')
            ->whereNotNull('powerbi_client_secret')->where('powerbi_client_secret', '!=', '')
            ->exists();
        if (! $hasConfig) {
            return back()->with('error', 'Primero configura credenciales en Configuraciones.');
        }
        try {
            $svc = app(PowerBiCapacityService::class);
            $res = $svc->importCapacities();
            // Reseteamos el timestamp de última sync para que no frene futuros autosync
            Cache::forget('capacities:last_sync_at');

            return back()->with(
                'success',
                "Refrescado: nuevas {$res['created']}, actualizadas {$res['updated']}."
            );
        } catch (\Throwable $e) {
            Log::error('Capacities manual refresh error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Error al refrescar: ' . $e->getMessage());
        }
    }

    /**
     * Pausar capacidad via ARM.
     */
    public function pause(Capacity $capacity): RedirectResponse
    {
        try {
            $hasConfig = Configuracion::query()
                ->whereNotNull('powerbi_tenant_id')->where('powerbi_tenant_id', '!=', '')
                ->whereNotNull('powerbi_client_id')->where('powerbi_client_id', '!=', '')
                ->whereNotNull('powerbi_client_secret')->where('powerbi_client_secret', '!=', '')
                ->exists();
            if (! $hasConfig) {
                return back()->with('error', 'No hay configuración activa para pausar capacidades.');
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
                'message'     => 'Paused by user',
            ]);

            // Al cambiar estado, borramos la marca para permitir una próxima sync fresca
            Cache::forget('capacities:last_sync_at');

            return back()->with('success', "Capacidad '{$capacity->display_name}' pausada.");
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
            return back()->with('error', "No se pudo pausar: {$e->getMessage()}");
        }
    }

    /**
     * Reanudar capacidad via ARM.
     */
    public function resume(Capacity $capacity): RedirectResponse
    {
        try {
            $hasConfig = Configuracion::query()
                ->whereNotNull('powerbi_tenant_id')->where('powerbi_tenant_id', '!=', '')
                ->whereNotNull('powerbi_client_id')->where('powerbi_client_id', '!=', '')
                ->whereNotNull('powerbi_client_secret')->where('powerbi_client_secret', '!=', '')
                ->exists();
            if (! $hasConfig) {
                return back()->with('error', 'No hay configuración activa para reanudar capacidades.');
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
                'message'     => 'Resumed by user',
            ]);

            Cache::forget('capacities:last_sync_at');

            return back()->with('success', "Capacidad '{$capacity->display_name}' reanudada.");
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
            return back()->with('error', "No se pudo reanudar: {$e->getMessage()}");
        }
    }

    // ===== Programación (encendido/pausado) e inactividad =====
    public function schedule(Capacity $capacity): View
    {
        $capacity->load('schedules');
        $dayNames = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
        return view('capacities.schedule', [
            'capacity'  => $capacity,
            'schedules' => $capacity->schedules()->orderBy('weekday')->orderBy('start_time')->get(),
            'dayNames'  => $dayNames,
        ]);
    }

    public function scheduleStore(Request $request, Capacity $capacity): RedirectResponse
    {
        $validated = $request->validate([
            'days'       => ['required','array','min:1'],
            'days.*'     => ['integer','between:0,6'],
            'start_time' => ['required','date_format:H:i'],
            'end_time'   => ['required','date_format:H:i','after:start_time'],
        ]);

        $start = $validated['start_time'] . ':00';
        $end   = $validated['end_time']   . ':00';
        foreach (array_unique($validated['days']) as $wd) {
            $capacity->schedules()->create([
                'weekday'    => (int)$wd,
                'start_time' => $start,
                'end_time'   => $end,
                'enabled'    => true,
            ]);
        }
        return back()->with('success','Horario agregado.');
    }

    public function scheduleToggle(Capacity $capacity, int $slot): RedirectResponse
    {
        $slot = \App\Models\CapacitySchedule::query()->where('capacity_id',$capacity->id)->where('id',$slot)->firstOrFail();
        $slot->enabled = ! $slot->enabled;
        $slot->save();
        return back()->with('success', $slot->enabled ? 'Horario activado.' : 'Horario desactivado.');
    }

    public function scheduleDelete(Capacity $capacity, int $slot): RedirectResponse
    {
        \App\Models\CapacitySchedule::query()->where('capacity_id',$capacity->id)->where('id',$slot)->delete();
        return back()->with('success','Horario eliminado.');
    }

    public function settingsUpdate(Request $request, Capacity $capacity): RedirectResponse
    {
        $validated = $request->validate([
            'automation_enabled'          => ['nullable','boolean'],
            'inactivity_timeout_minutes'  => ['nullable','integer','min:5','max:120'],
        ]);

        // Switch unificado: fuerza ambos flags en tándem
        $auto = $request->boolean('automation_enabled');
        $capacity->force_shutdown_on_schedule = $auto;
        $capacity->cost_optimization_enabled  = $auto;
        $capacity->inactivity_timeout_minutes = $validated['inactivity_timeout_minutes'] ?? null;

        // Si se activa la automatización y no hay timeout, usar 30m por defecto
        if ($auto && empty($capacity->inactivity_timeout_minutes)) {
            $capacity->inactivity_timeout_minutes = 30;
        }
        $capacity->save();

        return back()->with('success','Configuración actualizada.');
    }

    public function audit(Capacity $capacity): View
    {
        // 1) Logs locales (BD)
        $localRaw = \App\Models\CapacityAudit::query()->where('capacity_id',$capacity->id)
            ->orderByDesc('id')->limit(200)->get();

        // Normalizamos locales a objetos simples con campo source
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

        // 2) Logs de API (Azure Activity Logs / ARM)
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

                // Creamos objetos compatibles con la vista (propiedades como en el modelo)
                $row = new class($ts, $action, $result, $message) {
                    public $created_at; public $action; public $result; public $message; public $source;
                    public function __construct($created_at, $action, $result, $message){
                        $this->created_at = $created_at; $this->action = $action; $this->result = $result; $this->message = $message; $this->source = 'api';
                    }
                };
                $apiItems->push($row);
            }
        } catch (\Throwable $e) {
            // Si falla (sin credenciales ARM, etc.), seguimos solo con locales
        }

        // 3) Merge + ordenar desc por fecha
        $logs = $apiItems
            ->merge($local)
            ->sortByDesc(function ($x) { return optional($x->created_at)->getTimestamp() ?? 0; })
            ->take(200);

        return view('capacities.audit', compact('capacity','logs'));
    }

    /**
     * Refresca metadatos de la capacidad desde Power BI y vuelve a la auditoría.
     */
    public function auditRefresh(Capacity $capacity): \Illuminate\Http\RedirectResponse
    {
        $hasConfig = \App\Models\Configuracion::query()
            ->whereNotNull('powerbi_tenant_id')->where('powerbi_tenant_id', '!=', '')
            ->whereNotNull('powerbi_client_id')->where('powerbi_client_id', '!=', '')
            ->whereNotNull('powerbi_client_secret')->where('powerbi_client_secret', '!=', '')
            ->exists();
        if (! $hasConfig) {
            return back()->with('error', 'Primero configura credenciales en Configuraciones.');
        }

        try {
            /** @var PowerBiCapacityService $svc */
            $svc = app(PowerBiCapacityService::class);
            $r   = $svc->getCapacity($capacity->capacity_uuid);

            if (empty($r)) {
                return back()->with('error', 'No se pudo obtener metadatos de la capacidad.');
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

            return redirect()->route('capacities.audit', $capacity)->with('success', 'Metadatos actualizados.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al actualizar metadatos: ' . $e->getMessage());
        }
    }

    /**
     * Sincroniza contra Fabric/Power BI si:
     *   - No se sincroniza hace < $windowSeconds
     *   - Y logra tomar un lock para evitar concurrencia
     *
     * Garantiza:
     *   - No cachea fallas: solo setea el timestamp si la sync fue OK.
     *   - No rompe la navegación si import falla.
     */
    private function syncWithThrottle(PowerBiCapacityService $svc, int $windowSeconds = 120): void
    {
        try {
            $last = Cache::get('capacities:last_sync_at');
            if ($last && now()->diffInSeconds($last) < $windowSeconds) {
                // Dentro de ventana: no sincronizamos
                return;
            }

            // Evita sincronizaciones concurrentes (lock expira en windowSeconds)
            $lock = Cache::lock('capacities:syncing', $windowSeconds);

            if ($lock->get()) {
                try {
                    $svc->importCapacities(); // ← tu sync real con Fabric/Power BI
                    // Marcamos última sync SOLO si el import fue OK
                    Cache::put('capacities:last_sync_at', now(), $windowSeconds);
                } finally {
                    optional($lock)->release();
                }
            } else {
                // Otro proceso ya está sincronizando; no hacemos nada.
                return;
            }
        } catch (\Throwable $e) {
            Log::warning('Capacities auto-sync failed: '.$e->getMessage(), ['exception' => $e]);
            // No interrumpimos la vista si falla la sync.
        }
    }
}
