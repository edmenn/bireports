<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\ProjectAuditCommand;
use App\Console\Commands\SyncEntraUsers;
use App\Console\Commands\UiAudit;
use App\Console\Commands\UiPlan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

final class Kernel extends ConsoleKernel
{
    /**
     * Registrar comandos de consola de forma explícita.
     *
     * @var array<class-string>
     */
    protected $commands = [
        ProjectAuditCommand::class,
        SyncEntraUsers::class, // comando de sync Entra (si lo tienes creado)
        UiAudit::class,        // auditoría de stack UI (AdminLTE/Bootstrap/jQuery/plugins)
        UiPlan::class,         // plan de migración a Argon (Bootstrap 5) basado en ui_audit.json
    ];

    /**
     * Zona horaria aplicada al scheduler.
     */
    protected function scheduleTimezone(): \DateTimeZone|string|null
    {
        return \config('app.timezone'); // p.ej.: 'America/Sao_Paulo'
    }

    /**
     * Definición de tareas programadas.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Jobs Power BI existentes
        $schedule->command('powerbi:capacities:import')
            ->hourly()
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('powerbi:capacities:evaluate')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onOneServer();

        // (Opcional) Auditoría semanal automática del stack UI
        // Genera/actualiza storage/app/ui_audit.json para tener visibilidad continua.
        // $schedule->command('ui:audit')
        //     ->weeklyOn(1, '03:10') // lunes 03:10
        //     ->withoutOverlapping()
        //     ->onOneServer();

        // (Opcional) Programar la sincronización de usuarios Entra diariamente.
        // Ajusta parámetros según tu necesidad o deja comentado si prefieres manual.
        // $schedule->command('users:sync-entra --domain=sarcom.com.py')
        //     ->dailyAt('02:10')
        //     ->withoutOverlapping()
        //     ->onOneServer();
    }

    /**
     * Registro de comandos y rutas de consola.
     */
    protected function commands(): void
    {
        // Autocargar todos los comandos del directorio App\Console\Commands
        $this->load(__DIR__ . '/Commands');

        // Rutas de consola (si las usas)
        require \base_path('routes/console.php');
    }
}
