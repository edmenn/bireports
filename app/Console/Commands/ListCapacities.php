<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PowerBiCapacityService;
use Illuminate\Console\Command;

class ListCapacities extends Command
{
    /**
     * Nombre del comando Artisan.
     *
     * @var string
     */
    protected $signature = 'powerbi:capacities:list';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Lista todas las capacidades visibles en el tenant (GUID, nombre, SKU, estado).';

    public function handle(PowerBiCapacityService $svc): int
    {
        try {
            $caps = $svc->listCapacities();

            if (empty($caps)) {
                $this->warn('No se encontraron capacidades en este tenant con este Service Principal.');
                return self::SUCCESS;
            }

            $rows = [];
            foreach ($caps as $c) {
                $rows[] = [
                    'ID (GUID)'   => $c['id'] ?? '',
                    'Nombre'      => $c['displayName'] ?? $c['name'] ?? '',
                    'SKU'         => $c['sku'] ?? '',
                    'Estado'      => $c['state'] ?? '',
                    'Región'      => $c['region'] ?? '',
                ];
            }

            $this->table(
                ['ID (GUID)', 'Nombre', 'SKU', 'Estado', 'Región'],
                $rows
            );

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error al obtener capacidades: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
