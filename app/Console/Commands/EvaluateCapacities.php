<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\CheckCapacityInactivityJob;
use App\Jobs\EvaluateCapacitySchedulesJob;
use App\Models\Capacity;
use Illuminate\Console\Command;

class EvaluateCapacities extends Command
{
    protected $signature = 'powerbi:capacities:evaluate';
    protected $description = 'Evalúa horarios e inactividad de todas las capacidades';

    public function handle(): int
    {
        $caps = Capacity::all();

        foreach ($caps as $cap) {
            EvaluateCapacitySchedulesJob::dispatch($cap->id);
            CheckCapacityInactivityJob::dispatch($cap->id);
        }

        $this->info("Se enviaron jobs de evaluación para {$caps->count()} capacidades.");
        return self::SUCCESS;
    }
}
