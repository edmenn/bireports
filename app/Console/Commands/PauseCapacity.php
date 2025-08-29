<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Capacity;
use App\Services\PowerBiCapacityService;
use Illuminate\Console\Command;

class PauseCapacity extends Command
{
    protected $signature = 'powerbi:capacities:pause {id : GUID de la capacidad (columna ID del listado)}';
    protected $description = 'Pausa una capacidad por su GUID usando los endpoints de admin.';

    public function handle(PowerBiCapacityService $svc): int
    {
        $id = (string) $this->argument('id');
        $this->info("Pausando capacidad {$id}…");

        try {
            $svc->pause($id);

            /** @var Capacity|null $cap */
            $cap = Capacity::query()->where('capacity_uuid', $id)->first();
            if ($cap) {
                $cap->is_active = false;
                $cap->last_state_change_at = now();
                $cap->save();
            }

            $this->info('OK: petición enviada (la API típicamente responde 200/202/204).');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('ERROR pausando capacidad: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
