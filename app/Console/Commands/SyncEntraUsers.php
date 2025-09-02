<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\EntraUserImporter;
use Illuminate\Console\Command;

final class SyncEntraUsers extends Command
{
    /**
     * Comando:
     *  php artisan users:sync-entra
     *
     * Opciones:
     *  --query=         Prefijo para filtrar por startsWith(displayName|UPN) en Graph.
     *  --domain=        Importar solo correos/UPN que terminen en este dominio (ej: empresa.com).
     *  --assign-role=   Rol a asignar al crear (Spatie). No afecta a usuarios existentes.
     *  --dry-run        Simula sin escribir cambios en la BD.
     */
    protected $signature = 'users:sync-entra
                            {--query= : Filtro por prefijo (displayName/UPN)}
                            {--domain= : Importar solo este dominio (ej: empresa.com)}
                            {--assign-role= : Rol a asignar al crear}
                            {--dry-run : No persiste cambios, solo simula}';

    protected $description = 'Sincroniza usuarios desde Microsoft Entra (Graph) hacia la base local';

    public function __construct(
        private readonly EntraUserImporter $importer
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $query      = $this->option('query') ? (string)$this->option('query') : null;
        $domain     = $this->option('domain') ? (string)$this->option('domain') : null;
        $assignRole = $this->option('assign-role') ? (string)$this->option('assign-role') : null;
        $dryRun     = (bool)$this->option('dry-run');

        $this->info('=== Sync Entra Users ===');
        $this->line('Query: ' . ($query ?? '(sin filtro)'));
        $this->line('Domain: ' . ($domain ?? '(todos)'));
        $this->line('Assign role: ' . ($assignRole ?? '(ninguno)'));
        $this->line('Dry run: ' . ($dryRun ? 'sí' : 'no'));

        try {
            $res = $this->importer->sync($query, $domain, $assignRole, $dryRun);
        } catch (\Throwable $e) {
            $this->error('Error consultando Microsoft Graph: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();
        $this->table(
            ['created', 'updated', 'skipped', 'errors'],
            [[(string)$res['created'], (string)$res['updated'], (string)$res['skipped'], (string)$res['errors']]]
        );

        if ((int)$res['errors'] > 0) {
            $this->warn('Hubo errores. Revisa storage/logs/laravel.log');
        }

        return self::SUCCESS;
    }
}
