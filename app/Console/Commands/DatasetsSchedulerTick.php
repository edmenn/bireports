<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Dataset;
use App\Models\DatasetSchedule;
use App\Models\Workspace;
use App\Models\Capacity;
use App\Services\PowerBiCapacityService;
use App\Services\PowerBiService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DatasetsSchedulerTick extends Command
{
    protected $signature = 'datasets:scheduler-tick';

    protected $description = 'Evalúa programaciones locales de datasets y dispara refresh cuando corresponde.';

    public function handle(PowerBiService $pbi, PowerBiCapacityService $caps): int
    {
        $tz = \App\Support\AppTimezone::current();
        $now = Carbon::now($tz);
        $nowKey = $now->format('Y-m-d H:i'); // resolución por minuto
        $nowDow = $now->dayOfWeekIso; // 1..7
        $mapDow = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'];
        $nowDayName = $mapDow[$nowDow] ?? 'Monday';
        $nowTime = $now->format('H:i');

        DatasetSchedule::query()
            ->where('enabled', true)
            ->chunkById(200, function ($chunk) use ($nowKey, $nowDayName, $nowTime, $pbi, $caps) {
                foreach ($chunk as $sch) {
                    /** @var DatasetSchedule $sch */
                    $days  = (array) $sch->days;
                    $times = (array) $sch->times;

                    if (!in_array($nowDayName, $days, true)) continue;
                    if (!in_array($nowTime, $times, true)) continue;

                    // Evitar doble ejecución en el mismo minuto
                    if ($sch->last_run_at && $sch->last_run_at->format('Y-m-d H:i') === $nowKey) {
                        continue;
                    }

                    $dataset = Dataset::find($sch->dataset_id);
                    if (!$dataset) continue;
                    $ws = Workspace::find($dataset->workspace_id);
                    if (!$ws) continue;

                    $status = 'success';
                    $message = 'OK';

                    // Reanudar capacidad si corresponde
                    if (!empty($ws->capacity_id)) {
                        $cap = Capacity::query()->where('capacity_uuid', $ws->capacity_id)->first();
                        if ($cap && $cap->isPausable() && $cap->is_active === false) {
                            try { 
                                $caps->resume($cap);
                            } catch (\Throwable $e) {
                                $status = 'error';
                                $message = 'Resume failed: ' . $e->getMessage();
                            }
                        }
                    }

                    // Disparar refresh si no falló el resume
                    if ($status === 'success') {
                        try {
                            $pbi->refreshDataset($ws->id, $dataset->id);
                        } catch (\Throwable $e) {
                            $status = 'error';
                            $message = 'Refresh failed: ' . $e->getMessage();
                        }
                    }

                    $sch->last_run_at = now();
                    $sch->last_run_status = $status;
                    $sch->last_run_message = $message;
                    $sch->save();
                }
            });

        return self::SUCCESS;
    }
}
