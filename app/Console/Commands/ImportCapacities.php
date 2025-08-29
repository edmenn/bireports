<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PowerBiCapacityService;
use Illuminate\Console\Command;

class ImportCapacities extends Command
{
    protected $signature = 'powerbi:capacities:import';
    protected $description = 'Importa/actualiza todas las capacidades desde Power BI/Fabric';

    public function handle(PowerBiCapacityService $svc): int
    {
        $res = $svc->importCapacities();
        $this->info("Importadas capacidades. Nuevas: {$res['created']} | Actualizadas: {$res['updated']}");

        return self::SUCCESS;
    }
}
