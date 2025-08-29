<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Capacity;
use App\Models\CapacityActivity;
use App\Services\PowerBiCapacityService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CheckInactivity extends Command
{
    protected $signature = 'powerbi:capacities:inactivity 
        {--dry-run=0 : Simula sin pausar realmente} 
        {--only= : Filtra por GUID o arm_resource_name (coma-separado)}';

    protected $description = 'Pausa capacidades activas que superaron el umbral de inactividad (cost optimization). 0 minutos = desactivado.';

    /** Valores permitidos de timeout (minutos) cuando está activo */
    private const ALLOWED_TIMEOUTS = [5, 10, 30, 60, 90, 180];

    public function handle(PowerBiCapacityService $svc): int
    {
        $dryRun = (bool) (int) $this->option('dry-run');
        $only   = trim((string) $this->option('only'));

        // Construcción del query base
        $query = Capacity::query()
            ->with(['activity'])
            ->where('supports_pause', true)
            ->where('is_active', true)
            ->where('cost_optimization_enabled', true)
            // Aceptamos 0 (desactivado) y los valores permitidos
            ->whereIn('inactivity_timeout_minutes', array_merge(self::ALLOWED_TIMEOUTS, [0]));

        // Filtro opcional por lista de IDs o nombres ARM
        if ($only !== '') {
            $keys = array_filter(array_map('trim', explode(',', $only)));
            $query->where(function ($q) use ($keys) {
                foreach ($keys as $k) {
                    $q->orWhere('capacity_uuid', $k)
                      ->orWhereRaw('LOWER(arm_resource_name) = ?', [mb_strtolower($k)]);
                }
            });
        }

        /** @var \Illuminate\Support\Collection<int, Capacity> $caps */
        $caps = $query->get();

        if ($caps->isEmpty()) {
            $this->info('No hay capacidades candidatas para auto–pausa.');
            return self::SUCCESS;
        }

        $rows   = [];
        $paused = 0;
        $skipped = 0;

        foreach ($caps as $c) {
            $timeout = (int) $c->inactivity_timeout_minutes;
            $act     = $c->activity instanceof CapacityActivity ? $c->activity : null;

            $lastSeen = $act?->last_seen_at ? Carbon::parse($act->last_seen_at) : null;

            // Si timeout = 0 => desactivado: nunca pausar
            if ($timeout === 0) {
                $rows[] = [
                    'Nombre'   => $c->display_name,
                    'GUID'     => $c->capacity_uuid,
                    'ARM'      => $c->arm_resource_name ?? '-',
                    'SKU'      => $c->sku,
                    'Timeout'  => 'Desactivado',
                    'Últ. uso' => $lastSeen?->diffForHumans() ?? '(nunca)',
                    'Acción'   => '---',
                ];
                $skipped++;
                continue;
            }

            // Validación defensiva por si hay valores fuera de catálogo
            if (!in_array($timeout, self::ALLOWED_TIMEOUTS, true)) {
                $rows[] = [
                    'Nombre'   => $c->display_name,
                    'GUID'     => $c->capacity_uuid,
                    'ARM'      => $c->arm_resource_name ?? '-',
                    'SKU'      => $c->sku,
                    'Timeout'  => "Inválido ({$timeout})",
                    'Últ. uso' => $lastSeen?->diffForHumans() ?? '(nunca)',
                    'Acción'   => '---',
                ];
                $skipped++;
                continue;
            }

            // Umbral de inactividad
            $threshold   = Carbon::now()->subMinutes($timeout);
            $shouldPause = ($lastSeen === null) || ($lastSeen->lt($threshold));

            $rows[] = [
                'Nombre'   => $c->display_name,
                'GUID'     => $c->capacity_uuid,
                'ARM'      => $c->arm_resource_name ?? '-',
                'SKU'      => $c->sku,
                'Timeout'  => "{$timeout}m",
                'Últ. uso' => $lastSeen?->diffForHumans() ?? '(nunca)',
                'Acción'   => $shouldPause ? ($dryRun ? 'PAUSAR (dry-run)' : 'PAUSAR') : '---',
            ];

            if (! $shouldPause) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                continue; // solo simula
            }

            // Pausar por ARM y reflejar estado local
            DB::beginTransaction();
            try {
                $svc->pause($c); // Usa ARM + subscription/RG + arm_resource_name
                $c->is_active = false;
                $c->last_state_change_at = now();
                $c->save();

                if ($act) {
                    $act->hits_last_window = 0;
                    $act->save();
                }

                DB::commit();
                $paused++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error("Error pausando '{$c->display_name}': " . $e->getMessage());
            }
        }

        $this->table(
            ['Nombre', 'GUID', 'ARM', 'SKU', 'Timeout', 'Últ. uso', 'Acción'],
            $rows
        );

        $this->info("Auto–pausa finalizada. Pausadas: {$paused}. Omitidas: {$skipped}." . ($dryRun ? ' (dry-run)' : ''));
        return self::SUCCESS;
    }
}
