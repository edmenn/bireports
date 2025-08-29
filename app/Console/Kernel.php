<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\ProjectAuditCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

final class Kernel extends ConsoleKernel
{
    /**
     * Registrar comandos de consola.
     * Aunque Laravel autodetecta comandos en App\Console\Commands,
     * los registramos explícitamente para evitar sorpresas.
     *
     * @var array<class-string>
     */
    protected $commands = [
        ProjectAuditCommand::class,
    ];

    /**
     * Zona horaria aplicada al scheduler.
     * Asegura que las tareas se ejecuten según config('app.timezone').
     */
    protected function scheduleTimezone(): \DateTimeZone|string|null
    {
        return config('app.timezone'); // p.ej.: 'America/Sao_Paulo'
    }

    /**
     * Definición de tareas programadas.
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

        // Si usas rutas de consola (útil para definir schedules en un solo lugar)
        require base_path('routes/console.php');
    }
}
