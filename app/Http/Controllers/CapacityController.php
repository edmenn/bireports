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
                $like = '%' . $q . '%';
                // Si usas MySQL, cambia 'ilike' por 'like'
                $qr->where(function ($w) use ($like) {
                    $w->where('display_name', 'ilike', $like)
                      ->orWhere('capacity_uuid', 'ilike', $like)
                      ->orWhere('region', 'ilike', $like)
                      ->orWhere('sku', 'ilike', $like);
                });
            })
            ->when($state === 'active', fn ($qr) => $qr->where('is_active', true))
            ->when($state === 'paused', fn ($qr) => $qr->where('is_active', false))
            ->when($sku !== '', fn ($qr) => $qr->where('sku', 'ilike', '%' . $sku . '%'))
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

            // Al cambiar estado, borramos la marca para permitir una próxima sync fresca
            Cache::forget('capacities:last_sync_at');

            return back()->with('success', "Capacidad '{$capacity->display_name}' pausada.");
        } catch (\Throwable $e) {
            Log::error('Pause capacity error: '.$e->getMessage(), [
                'capacity_id' => $capacity->id,
                'exception'   => $e,
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

            Cache::forget('capacities:last_sync_at');

            return back()->with('success', "Capacidad '{$capacity->display_name}' reanudada.");
        } catch (\Throwable $e) {
            Log::error('Resume capacity error: '.$e->getMessage(), [
                'capacity_id' => $capacity->id,
                'exception'   => $e,
            ]);
            return back()->with('error', "No se pudo reanudar: {$e->getMessage()}");
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
