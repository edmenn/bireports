<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ServerReportCommand extends Command
{
    protected $signature = 'server:report';
    protected $description = 'Muestra un reporte del servidor (disco) y base de datos (tipo y tamaño)';

    public function handle(): int
    {
        $this->info('=== Reporte del Servidor ===');

        // --- 1) Disco disponible (robusto / Docker / WSL / Linux/Windows) ---
        $pathsToTry = array_filter([
            realpath(base_path()) ?: null,
            DIRECTORY_SEPARATOR, // "/"
        ]);

        $diskPrinted = false;
        foreach ($pathsToTry as $path) {
            $total = @disk_total_space($path);  // int|float|false
            $free  = @disk_free_space($path);   // int|float|false

            if ($total !== false && $free !== false && $total > 0) {
                $totalF = (float) $total;
                $freeF  = (float) $free;
                $usedF  = $totalF - $freeF;

                $this->line("Path de medición: {$path}");
                $this->line('Disco total: ' . $this->formatBytes($totalF));
                $this->line('Disco usado: ' . $this->formatBytes($usedF));
                $this->line('Disco libre: ' . $this->formatBytes($freeF));
                $diskPrinted = true;
                break;
            }
        }

        if (!$diskPrinted) {
            $this->warn('No fue posible obtener el espacio en disco (disk_total_space/disk_free_space devolvió false).');
        }

        // --- 2) Tipo de BD actual ---
        $driver = DB::connection()->getDriverName(); // mysql|pgsql|sqlite|sqlsrv
        $this->line("Tipo de BD: {$driver}");

        // --- 3) Tamaño de la base de datos (por motor) ---
        try {
            [$dbName, $dbBytes] = $this->getDatabaseSize($driver);
            if ($dbName !== null && $dbBytes !== null) {
                $this->line("Base de datos: {$dbName}");
                $this->line('Tamaño BD: ' . $this->formatBytes($dbBytes));
            } else {
                $this->warn('No se pudo determinar el tamaño de la base de datos para este driver.');
            }
        } catch (\Throwable $e) {
            Log::error('Error obteniendo tamaño de BD', ['ex' => $e]);
            $this->error('Error obteniendo tamaño de la base de datos: ' . $e->getMessage());
        }

        return self::SUCCESS;
    }

    /**
     * Devuelve [nombreBD, tamañoEnBytes] o [null, null] si no aplica.
     */
    private function getDatabaseSize(string $driver): array
    {
        return match ($driver) {
            'pgsql'  => $this->sizePostgres(),
            'mysql'  => $this->sizeMySQL(),
            'sqlite' => $this->sizeSqlite(),
            'sqlsrv' => $this->sizeSqlServer(),
            default  => [null, null],
        };
    }

    /**
     * PostgreSQL: usa pg_database_size(current_database()).
     */
    private function sizePostgres(): array
    {
        $row = DB::selectOne('select current_database() as db, pg_database_size(current_database())::bigint as bytes');
        if (!$row) {
            return [null, null];
        }
        $dbName = (string) ($row->db ?? '');
        $bytes  = (float) ($row->bytes ?? 0);
        return [$dbName, $bytes];
    }

    /**
     * MySQL/MariaDB: suma data_length + index_length de information_schema.tables.
     */
    private function sizeMySQL(): array
    {
        // Nombre de BD actual
        $dbRow = DB::selectOne('select database() as db');
        if (!$dbRow || empty($dbRow->db)) {
            return [null, null];
        }
        $dbName = (string) $dbRow->db;

        $row = DB::selectOne(
            'select sum(data_length + index_length) as bytes
               from information_schema.tables
              where table_schema = database()'
        );
        $bytes = (float) (($row && isset($row->bytes)) ? $row->bytes : 0);
        return [$dbName, $bytes];
    }

    /**
     * SQLite: el "tamaño" es el tamaño del archivo de base de datos.
     */
    private function sizeSqlite(): array
    {
        /** @var array{database:string}|null $cfg */
        $cfg = DB::connection()->getConfig();
        $path = isset($cfg['database']) ? (string)$cfg['database'] : '';
        if ($path === '' || !is_file($path)) {
            return [basename($path) ?: 'sqlite', null];
        }
        $bytes = (float) filesize($path);
        return [basename($path), $bytes];
    }

    /**
     * SQL Server: suma size * 8KB de sys.database_files.
     */
    private function sizeSqlServer(): array
    {
        $dbRow = DB::selectOne('select db_name() as db');
        if (!$dbRow || empty($dbRow->db)) {
            return [null, null];
        }
        $dbName = (string) $dbRow->db;

        $row = DB::selectOne('select cast(sum(size) * 8192 as bigint) as bytes from sys.database_files');
        $bytes = (float) (($row && isset($row->bytes)) ? $row->bytes : 0);
        return [$dbName, $bytes];
    }

    /**
     * Formatea bytes a una cadena legible (sin notación científica).
     */
    private function formatBytes(float $bytes): string
    {
        if ($bytes < 0) {
            $bytes = 0.0;
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024.0;
            $i++;
        }

        return number_format($bytes, 2, '.', '') . ' ' . $units[$i];
    }
}
