<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Capacity;
use App\Models\Configuracion;
use App\Models\Dataset;
use App\Models\Workspace;
use App\Services\PowerBiCapacityService;
use App\Services\PowerBiService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Reanuda (resume) la capacidad asociada a los workspaces de datasets
 * que tienen una ejecución programada próxima, para evitar fallas por capacidad pausada.
 */
class DatasetsPrewarm extends Command
{
    protected $signature = 'powerbi:datasets:prewarm {--window=10 : Minutos de anticipación para encender la capacidad}';

    protected $description = 'Prepara (resume) la capacidad antes de ejecuciones programadas de datasets en Power BI.';

    public function handle(PowerBiService $pbi, PowerBiCapacityService $caps): int
    {
        $cfg = Configuracion::query()->first();
        if (!($cfg?->prewarm_enabled)) {
            $this->info('Pre-warm deshabilitado por configuraciones. Nada que hacer.');
            return self::SUCCESS;
        }
        $tz  = $cfg?->app_timezone_db ?: config('app.timezone');
        $windowMin = (int) $this->option('window');
        $windowMin = $windowMin > 0 ? $windowMin : 10;

        $nowTz = Carbon::now($tz);
        $endTz = (clone $nowTz)->addMinutes($windowMin);

        // Mapa rápido de capacities por UUID
        $capsById = Capacity::query()->get()->keyBy('capacity_uuid');

        // Iterar datasets (paginado por chunks para evitar memoria)
        Dataset::query()->orderBy('workspace_id')->chunkById(200, function ($chunk) use ($pbi, $caps, $tz, $nowTz, $endTz, $capsById) {
            foreach ($chunk as $ds) {
                /** @var Dataset $ds */
                $ws = Workspace::find($ds->workspace_id);
                if (!$ws || empty($ws->capacity_id)) {
                    continue; // sin capacidad asociada
                }

                $cap = $capsById->get($ws->capacity_id);
                if (!$cap) {
                    continue; // capacidad no importada aún
                }

                // Si ya está activa y es pausable, nos ahorramos llamada
                if ($cap->is_active === true) {
                    continue;
                }

                // Leer schedule del dataset desde API (no persistimos por ahora)
                try {
                    $sched = $pbi->getDatasetRefreshSchedule($ws->id, $ds->id);
                } catch (\Throwable $e) {
                    continue;
                }

                $val = (array) ($sched['value'] ?? []);
                if (!($val['enabled'] ?? false)) {
                    continue;
                }

                $tzId  = (string) ($val['localTimeZoneId'] ?? $tz);
                $days  = (array) ($val['days'] ?? []);
                $times = (array) ($val['times'] ?? []);

                if (empty($days) || empty($times)) {
                    continue;
                }

                $dowMap = [
                    'Sunday' => 0,
                    'Monday' => 1,
                    'Tuesday' => 2,
                    'Wednesday' => 3,
                    'Thursday' => 4,
                    'Friday' => 5,
                    'Saturday' => 6,
                ];

                // ¿Alguna corrida cae dentro de [now, now+window]?
                $now = Carbon::now($tzId);
                $end = (clone $now)->addMinutes($nowTz->diffInMinutes($endTz));

                $hasUpcoming = false;
                foreach ($days as $day) {
                    $dowi = $dowMap[$day] ?? null;
                    if ($dowi === null) continue;

                    // hoy o próximos 2 días (para capturar ventanas cortas sobre medianoche)
                    for ($offset = 0; $offset <= 2; $offset++) {
                        $date = (clone $now)->startOfDay()->addDays($offset);
                        if ((int) $date->dayOfWeek !== $dowi) continue;

                        foreach ($times as $t) {
                            // Formato HH:mm
                            if (!preg_match('/^\d{2}:\d{2}$/', (string)$t)) continue;
                            [$hh, $mm] = array_map('intval', explode(':', (string)$t));
                            $runAt = (clone $date)->setTime($hh, $mm);
                            if ($runAt->betweenIncluded($now, $end)) {
                                $hasUpcoming = true; break 3;
                            }
                        }
                    }
                }

                if ($hasUpcoming && $cap->isPausable() && $cap->is_active === false) {
                    try {
                        $this->info("Resuming capacity {$cap->display_name} for dataset {$ds->name}");
                        $caps->resume($cap);
                    } catch (\Throwable $e) {
                        $this->error("Failed to resume capacity: " . $e->getMessage());
                    }
                }
            }
        });

        return self::SUCCESS;
    }
}
