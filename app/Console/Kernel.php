<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Definición de tareas programadas (puedes moverlas a routes/console.php si prefieres).
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('powerbi:capacities:import')
            ->hourly()
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('powerbi:capacities:evaluate')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onOneServer();
    }

    /**
     * Registro de comandos y rutas de consola.
     */
    protected function commands(): void
    {
        // Autocargar todos los comandos del directorio App\Console\Commands
        $this->load(__DIR__ . '/Commands');

        // Si usas rutas de consola (opcional, por claridad de schedule)
        require base_path('routes/console.php');
    }
}
