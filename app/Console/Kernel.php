<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\DispatchReportSubscriptions;
use App\Console\Commands\ProjectAuditCommand;
use App\Console\Commands\ResetAudit;
use App\Console\Commands\SyncEntraUsers;
use App\Console\Commands\UiAudit;
use App\Console\Commands\UiPlan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

final class Kernel extends ConsoleKernel
{
    /**
     * Explicitly register console commands while preserving existing behaviour.
     */
    protected $commands = [
        ProjectAuditCommand::class,
        DispatchReportSubscriptions::class,
        ResetAudit::class,
        SyncEntraUsers::class, // Entra sync command (if present in the installation)
        UiAudit::class,        // UI stack audit (AdminLTE/Bootstrap/jQuery/plugins)
        UiPlan::class,         // Migration plan to Argon (Bootstrap 5) based on ui_audit.json
    ];

    /**
     * Scheduler timezone.
     */
    protected function scheduleTimezone(): \DateTimeZone|string|null
    {
        try {
            $cfgTz = optional(\App\Models\Configuration::query()->first())->app_timezone_db;
            return $cfgTz ?: \config('app.timezone');
        } catch (\Throwable $e) {
            return \config('app.timezone');
        }
    }

    /**
     * Scheduled tasks definition.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Periodically import capacities (unchanged behaviour)
        $schedule->command('powerbi:capacities:import')
            ->hourly()
            ->withoutOverlapping()
            ->onOneServer();

        // Evaluate capacity schedules and inactivity (unchanged behaviour)
        $schedule->command('powerbi:capacities:evaluate')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onOneServer();

        /* [Minimalist] Local dataset scheduler for the portal.
         * The portal is responsible for refreshes, so we keep a one-minute tick
         * to evaluate and trigger local refresh jobs.
         */
        $schedule->command('datasets:scheduler-tick')
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('subscriptions:dispatch')
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer();

        // Dataset pre-warm (only if enabled in Configurations)
        try {
            $cfg = \App\Models\Configuration::query()->first();
            if ($cfg && $cfg->prewarm_enabled) {
                $schedule->command('powerbi:datasets:prewarm --window=10')
                    ->everyFiveMinutes()
                    ->withoutOverlapping()
                    ->onOneServer();
            }
        } catch (\Throwable $e) {
            // Silently ignore when the configuration table is not available yet
        }

        // (Optional) Weekly automatic UI stack audit
        // $schedule->command('ui:audit')
        //     ->weeklyOn(1, '03:10')
        //     ->withoutOverlapping()
        //     ->onOneServer();

        // (Optional) Entra user sync
        // $schedule->command('users:sync-entra --domain=sarcom.com.py')
        //     ->dailyAt('02:10')
        //     ->withoutOverlapping()
        //     ->onOneServer();
    }

    /**
     * Register console commands and routes.
     */
    protected function commands(): void
    {
        // Auto-load every command located in App\Console\Commands
        $this->load(__DIR__ . '/Commands');

        // Console routes (if used)
        require \base_path('routes/console.php');
    }
}
