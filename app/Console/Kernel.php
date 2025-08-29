<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\EvaluateCapacities;
use App\Console\Commands\ImportCapacities;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Registra los comandos de Artisan de la aplicación.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        ImportCapacities::class,
        EvaluateCapacities::class,
    ];

    /**
     * Define el scheduler (cron) de la aplicación.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Importar/actualizar capacidades cada hora
        $schedule->command('powerbi:capacities:import')
            ->hourly()
            ->withoutOverlapping()
            ->onOneServer();

        // Evaluar horarios e inactividad cada 5 minutos
        $schedule->command('powerbi:capacities:evaluate')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onOneServer();
    }

    /**
     * Registro adicional de comandos (si luego agregas routes/console.php).
     */
    protected function commands(): void
    {
        // $this->load(__DIR__ . '/Commands');
        // require base_path('routes/console.php');
    }
}
