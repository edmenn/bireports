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
     * Mantiene el comportamiento existente.
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
        try {
            $cfgTz = optional(\App\Models\Configuracion::query()->first())->app_timezone_db;
            return $cfgTz ?: \config('app.timezone');
        } catch (\Throwable $e) {
            return \config('app.timezone');
        }
    }

    /**
     * Definición de tareas programadas.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Importar capacidades periódicamente (sin cambios)
        $schedule->command('powerbi:capacities:import')
            ->hourly()
            ->withoutOverlapping()
            ->onOneServer();

        // Evaluar horarios e inactividad de capacidades (sin cambios)
        $schedule->command('powerbi:capacities:evaluate')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onOneServer();

        /* [Minimalista] Scheduler de datasets locales del portal
         * La idea es usar 100% el portal, por eso dejamos el tick
         * cada minuto para evaluar y disparar refresh locales.
         */
        $schedule->command('datasets:scheduler-tick')
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer();

        // Pre-warm de datasets (sólo si está habilitado en Configuraciones)
        try {
            $cfg = \App\Models\Configuracion::query()->first();
            if ($cfg && $cfg->prewarm_enabled) {
                $schedule->command('powerbi:datasets:prewarm --window=10')
                    ->everyFiveMinutes()
                    ->withoutOverlapping()
                    ->onOneServer();
            }
        } catch (\Throwable $e) {
            // Silencioso: si no hay tabla/config todavía
        }

        // (Opcional) Auditoría semanal automática del stack UI
        // $schedule->command('ui:audit')
        //     ->weeklyOn(1, '03:10')
        //     ->withoutOverlapping()
        //     ->onOneServer();

        // (Opcional) Sync de usuarios Entra
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

