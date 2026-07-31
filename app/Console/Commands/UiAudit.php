<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

class UiAudit extends Command
{
    protected $signature = 'ui:audit {--format=table : table|json}';
    protected $description = 'Audita el stack UI (AdminLTE, Bootstrap, jQuery, plugins) y lista vistas afectadas';

    public function handle()
    {
        $base = base_path();
        $storageJson = storage_path('app/ui_audit.json');

        // --- composer.json
        $composer = $this->readJson($base.'/composer.json');
        $require = array_merge($composer['require'] ?? [], $composer['require-dev'] ?? []);
        $laravelVersion = $require['laravel/framework'] ?? null;

        // --- package.json
        $package = $this->readJson($base.'/package.json');
        $deps = array_merge($package['dependencies'] ?? [], $package['devDependencies'] ?? []);
        $bootstrapVersion = $deps['bootstrap'] ?? null;
        $jqueryVersion    = $deps['jquery'] ?? null;

        // Flags por dependencias
        $hasAdminltePkg = isset($require['jeroennoten/laravel-adminlte']) || isset($require['adminlte-laravel']);
        $hasDataTables  = $this->hasAnyKey($deps, ['datatables.net', 'datatables.net-bs4', 'datatables.net-bs5']);
        $hasSelect2     = $this->hasAnyKey($deps, ['select2']);
        $hasAlpine      = isset($deps['alpinejs']);
        $hasTailwind    = isset($deps['tailwindcss']);

        // --- Archivos y vistas
        $finder = new Finder();
        $finder->files()->in(resource_path('views'))->name('*.blade.php');

        $viewsAdminlteExtends = [];
        $viewsAdminlteAssets  = [];
        $viewsJqueryUsage     = [];
        $viewsBootstrap4Util  = []; // pistas de B4 (text-right/float-right)
        $viewsBootstrap5Util  = []; // pistas de B5 (text-end/float-end)

        foreach ($finder as $file) {
            $path = $file->getRealPath();
            $rel  = str_replace(resource_path('views').DIRECTORY_SEPARATOR, '', $path);
            $rel  = str_replace(DIRECTORY_SEPARATOR, '/', $rel);

            $content = file_get_contents($path);

            // Extiende adminlte::page
            if (preg_match('/@extends\\([\'"]adminlte::page[\'"]\\)/', $content)) {
                $viewsAdminlteExtends[] = $rel;
            }

            // Includes/uso de assets/strings de AdminLTE
            if (preg_match('/adminlte/i', $content)) {
                $viewsAdminlteAssets[] = $rel;
            }

            // jQuery típico
            if (preg_match('/\\$\\(|jquery/i', $content)) {
                $viewsJqueryUsage[] = $rel;
            }

            // Heurísticas de utilidades B4/B5
            if (preg_match('/text-right|float-right/', $content)) {
                $viewsBootstrap4Util[] = $rel;
            }
            if (preg_match('/text-end|float-end/', $content)) {
                $viewsBootstrap5Util[] = $rel;
            }
        }

        // Busca config/adminlte.php
        $hasAdminlteConfig = file_exists(config_path('adminlte.php'));

        // Busca CSS/JS con "adminlte" en resources
        $assetsFinder = new Finder();
        $assetsFinder->files()->in(resource_path())->name(['*.css','*.scss','*.js','*.ts']);
        $assetsAdminlte = [];
        foreach ($assetsFinder as $file) {
            $content = @file_get_contents($file->getRealPath()) ?: '';
            if (stripos($content, 'adminlte') !== false) {
                $assetsAdminlte[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getRealPath());
            }
        }

        // Resultado
        $report = [
            'timestamp' => now()->toDateTimeString(),
            'framework' => [
                'laravel'   => $laravelVersion,
            ],
            'frontend' => [
                'bootstrap' => $bootstrapVersion,
                'jquery'    => $jqueryVersion,
                'tailwind'  => $hasTailwind,
                'alpine'    => $hasAlpine,
                'datatables'=> $hasDataTables,
                'select2'   => $hasSelect2,
            ],
            'adminlte' => [
                'composer_dependency' => $hasAdminltePkg,
                'config_file'         => $hasAdminlteConfig,
                'views_extending'     => $viewsAdminlteExtends,
                'views_with_traces'   => array_values(array_unique($viewsAdminlteAssets)),
                'assets_with_traces'  => $assetsAdminlte,
            ],
            'views_usage' => [
                'jquery_views'        => array_values(array_unique($viewsJqueryUsage)),
                'bootstrap4_util'     => array_values(array_unique($viewsBootstrap4Util)),
                'bootstrap5_util'     => array_values(array_unique($viewsBootstrap5Util)),
            ],
            'counts' => [
                'views_total'         => iterator_count((new Finder())->files()->in(resource_path('views'))->name('*.blade.php')),
                'views_adminlte_ext'  => count($viewsAdminlteExtends),
                'views_adminlte_assets'=> count(array_unique($viewsAdminlteAssets)),
                'views_jquery'        => count(array_unique($viewsJqueryUsage)),
            ]
        ];

        // Guardar JSON
        @file_put_contents($storageJson, json_encode($report, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

        // Salida
        if ($this->option('format') === 'json') {
            $this->line(json_encode($report, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        } else {
            $this->info("Reporte guardado en: {$storageJson}");
            $this->table(
                ['Check', 'Valor'],
                [
                    ['Laravel', $laravelVersion ?? '-'],
                    ['Bootstrap (pkg.json)', $bootstrapVersion ?? '-'],
                    ['jQuery (pkg.json)', $jqueryVersion ?? '-'],
                    ['AdminLTE (composer)', $hasAdminltePkg ? 'sí' : 'no'],
                    ['config/adminlte.php', $hasAdminlteConfig ? 'sí' : 'no'],
                    ['Vistas con @extends(adminlte::page)', (string)count($viewsAdminlteExtends)],
                    ['Vistas con trazas de AdminLTE', (string)count(array_unique($viewsAdminlteAssets))],
                    ['Vistas con jQuery', (string)count(array_unique($viewsJqueryUsage))],
                    ['DataTables', $hasDataTables ? 'sí' : 'no'],
                    ['Select2', $hasSelect2 ? 'sí' : 'no'],
                ]
            );
        }

        return Command::SUCCESS;
    }

    private function readJson(string $path): array
    {
        if (!file_exists($path)) return [];
        $raw = @file_get_contents($path);
        if (!$raw) return [];
        $json = json_decode($raw, true);
        return is_array($json) ? $json : [];
    }

    private function hasAnyKey(array $arr, array $keys): bool
    {
        foreach ($keys as $k) {
            if (array_key_exists($k, $arr)) return true;
        }
        return false;
    }
}
