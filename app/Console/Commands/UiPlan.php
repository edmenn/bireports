<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UiPlan extends Command
{
    protected $signature = 'ui:plan {--target=argon : Objetivo de migración (argon)}';
    protected $description = 'Genera un plan de migración paso a paso a partir de storage/app/ui_audit.json';

    public function handle()
    {
        $target = $this->option('target') ?? 'argon';
        $auditPath = storage_path('app/ui_audit.json');

        if (!file_exists($auditPath)) {
            $this->error("No existe {$auditPath}. Primero ejecuta: php artisan ui:audit");
            return Command::FAILURE;
        }

        $audit = json_decode(file_get_contents($auditPath), true);
        if (!is_array($audit)) {
            $this->error("No se pudo leer el JSON de auditoría.");
            return Command::FAILURE;
        }

        $steps = $this->buildPlan($audit, $target);

        $md = "# Plan de migración UI → {$target}\n\n";
        foreach ($steps as $i => $s) {
            $md .= ($i+1).". {$s}\n";
        }
        $mdPath = storage_path('app/ui_plan.md');
        file_put_contents($mdPath, $md);

        $this->info("Plan generado en: {$mdPath}");
        $this->line($md);

        return Command::SUCCESS;
    }

    private function buildPlan(array $audit, string $target): array
    {
        $steps = [];
        $hasAdminlte = ($audit['adminlte']['composer_dependency'] ?? false) || ($audit['adminlte']['config_file'] ?? false);
        $viewsAdminlte = $audit['adminlte']['views_extending'] ?? [];
        $bootstrap = $audit['frontend']['bootstrap'] ?? null;
        $jquery = $audit['frontend']['jquery'] ?? null;
        $hasDT = $audit['frontend']['datatables'] ?? false;
        $hasS2 = $audit['frontend']['select2'] ?? false;
        $viewsB4 = $audit['views_usage']['bootstrap4_util'] ?? [];
        $viewsJq = $audit['views_usage']['jquery_views'] ?? [];

        // Base
        $steps[] = "Crear rama de trabajo: `git checkout -b feature/migracion-{$target}`.";
        $steps[] = "Agregar layout base limpio (navbar + sidebar + content) en `resources/views/layouts` usando Bootstrap 5.";
        $steps[] = "Crear componentes Blade reutilizables: `<x-card>`, `<x-table>`, `<x-form>` para acelerar el remarcado.";

        if ($hasAdminlte) {
            $countExt = count($viewsAdminlte);
            $lista = $this->shortList($viewsAdminlte);
            $steps[] = "Detectado AdminLTE. Vistas que extienden `adminlte::page` ({$countExt}): {$lista}. Migrar **por módulo** reemplazando `@extends('adminlte::page')` por `@extends('layouts.app')` y remarcando clases.";
            $steps[] = "Una vez migradas las vistas críticas, eliminar AdminLTE: `composer remove jeroennoten/laravel-adminlte` y borrar `config/adminlte.php` si no se usa.";
        } else {
            $steps[] = "No se detecta AdminLTE como dependencia. Revisar igualmente trazas en vistas y assets.";
        }

        // Bootstrap
        if (!$bootstrap || preg_match('/^[\^~]?4\\./', (string)$bootstrap)) {
            $steps[] = "Actualizar a **Bootstrap 5** (si aplica): reemplazar utilidades B4 → B5 (`text-right`→`text-end`, `float-right`→`float-end`, etc.). Vistas con utilidades B4: ".$this->shortList($viewsB4).".";
            $steps[] = "Eliminar jQuery si no es estrictamente necesario (B5 no lo requiere).";
        } else {
            $steps[] = "Proyecto ya con Bootstrap 5. Mantener consistencia y utilidades `*-end`, `*-start`.";
        }

        // jQuery
        if ($jquery || count($viewsJq) > 0) {
            $steps[] = "Revisar dependencias de **jQuery** en vistas: ".$this->shortList($viewsJq).". Sustituir por JS nativo o plugins compatibles con B5.";
        }

        // Plugins
        if ($hasDT) {
            $steps[] = "DataTables: usar paquetes **BS5** (`datatables.net-bs5`) y revisar inicializaciones (sin jQuery si usas versión moderna o con jQuery si mantienes).";
        }
        if ($hasS2) {
            $steps[] = "Select2: aplicar tema para **Bootstrap 5** o considerar alternativas (Choices.js/TomSelect) si querés remover jQuery.";
        }

        // Menú
        $steps[] = "Extraer el menú a `config/menu.php` (driven por config) y renderizarlo en el sidebar con `request()->routeIs()` para `active`.";

        // Auth
        $steps[] = "Crear layout `layouts/guest.blade.php` para login/registro y apuntar vistas de auth a ese layout (si usás Breeze/Fortify/Jetstream).";

        // Build
        $steps[] = "Actualizar `resources/js/app.js` y `resources/css/app.css` para importar Bootstrap 5, Icons y JS; correr `npm ci && npm run build`.";

        // QA
        $steps[] = "QA visual: dashboard, tablas, formularios, colapsos de sidebar, breakpoints, footer. Ajustar sombras, radios, espaciados.";

        // Cierre
        $steps[] = "Cuando todo esté migrado, limpiar residuos (CSS/JS viejos, vistas no usadas) y `composer dump-autoload -o`.";

        return $steps;
    }

    private function shortList(array $items, int $max = 8): string
    {
        $items = array_values(array_unique($items));
        $count = count($items);
        if ($count === 0) return '(ninguna)';
        $show = array_slice($items, 0, $max);
        return implode(', ', $show).($count > $max ? " +".($count-$max)." más" : '');
    }
}
