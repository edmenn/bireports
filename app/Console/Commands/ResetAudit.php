<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ResetAudit extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'audit:reset {--force : Ejecuta sin pedir confirmación}';

    /**
     * The console command description.
     */
    protected $description = 'Elimina todos los registros de auditoría y reinicia el contador de IDs.';

    public function handle(): int
    {
        if (! $this->option('force')) {
            $this->warn(__('audit.reset.description'));
            $this->line(__('audit.reset.confirm_help'));

            $input = $this->ask(__('audit.reset.confirm_label'));
            if ($input !== 'RESET') {
                $this->error(__('audit.admin.confirm_exact'));
                return self::INVALID;
            }
        }

        try {
            DB::beginTransaction();
            DB::statement('TRUNCATE TABLE audits RESTART IDENTITY');
            DB::commit();

            $this->info(__('audit.admin.reset_success'));
            return self::SUCCESS;
        } catch (Throwable $e) {
            DB::rollBack();
            $this->error(__('audit.admin.reset_error', ['message' => $e->getMessage()]));
            return self::FAILURE;
        }
    }
}

