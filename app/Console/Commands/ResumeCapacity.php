<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Capacity;
use App\Services\PowerBiCapacityService;
use Illuminate\Console\Command;

class ResumeCapacity extends Command
{
    protected $signature = 'powerbi:capacity:resume {id_or_name : GUID (capacity_uuid) o arm_resource_name}';
    protected $description = 'Reanuda una capacidad via ARM usando el modelo Capacity (GUID o ARM name).';

    public function handle(PowerBiCapacityService $svc): int
    {
        $key = (string) $this->argument('id_or_name');

        /** @var Capacity|null $cap */
        $cap = Capacity::query()
            ->where(function ($q) use ($key) {
                if ($this->isUuid($key)) {
                    $q->where('capacity_uuid', $key);
                }
                $q->orWhereRaw('LOWER(arm_resource_name) = ?', [mb_strtolower($key)]);
            })
            ->first();

        if (!$cap) {
            $this->error("No encontré capacidad por '{$key}' (ni capacity_uuid ni arm_resource_name).");
            return self::FAILURE;
        }

        try {
            $svc->resume($cap); // usa el modelo (ARM)
            $cap->is_active = true;
            $cap->last_state_change_at = now();
            $cap->save();

            $this->info("✔ Capacidad '{$cap->display_name}' reanudada correctamente.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error al reanudar: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function isUuid(string $v): bool
    {
        return (bool) preg_match(
            '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
            $v
        );
    }
}
