<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use ReflectionClass;

final class ProjectAuditCommand extends Command
{
    protected $signature = 'project:audit {--json : Salida en JSON}';

    protected $description = 'Audita consistencia multicliente, permisos, rutas, auditoría e i18n del proyecto.';

    public function handle(): int
    {
        $report = [
            'models' => $this->inspectModels(),
            'migrations' => $this->inspectMigrations(),
            'routes' => $this->inspectRoutes(),
            'controllers' => $this->inspectControllers(),
            'views' => $this->inspectViews(),
            'translations' => $this->inspectTranslations(),
            'bi_services' => $this->inspectBiServices(),
            'audits' => $this->inspectAudits(),
            'summary' => [],
        ];

        // Resumen
        $report['summary'] = [
            'models_without_tenant_trait' => count($report['models']['without_tenant_trait'] ?? []),
            'models_without_client_id_cast' => count($report['models']['without_client_id_cast'] ?? []),
            'migrations_missing_client_id' => count($report['migrations']['missing_client_id'] ?? []),
            'routes_without_auth' => count($report['routes']['without_auth'] ?? []),
            'routes_without_can' => count($report['routes']['without_can'] ?? []),
            'controllers_without_authorize' => count($report['controllers']['without_authorize_resource'] ?? []),
            'views_suspected_cross_tenant' => count($report['views']['suspected_cross_tenant'] ?? []),
            'missing_translation_keys' => count($report['translations']['missing_keys'] ?? []),
            'bi_services_warnings' => count($report['bi_services']['warnings'] ?? []),
            'audits_warnings' => count($report['audits']['warnings'] ?? []),
        ];

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->prettyPrint($report);
        }

        return self::SUCCESS;
    }

    /* ===================== MODELOS ===================== */

    private function inspectModels(): array
    {
        $modelsDir = app_path('Models');
        $files = File::exists($modelsDir) ? File::allFiles($modelsDir) : [];
        $withoutTenantTrait = [];
        $withoutClientIdCast = [];
        $all = [];

        foreach ($files as $f) {
            if ($f->getExtension() !== 'php') {
                continue;
            }
            $fqcn = $this->fqcnFromFile($f->getRealPath());
            if (! $fqcn) {
                continue;
            }

            try {
                if (! is_subclass_of($fqcn, Model::class)) {
                    continue;
                }

                $rc = new ReflectionClass($fqcn);
                if ($rc->isAbstract()) {
                    continue;
                }

                $all[] = $fqcn;

                $src = File::get($f->getRealPath());
                $hasTenantTrait = (bool) preg_match('/use\s+[^;]*BelongsToTenant[^;]*;/', $src);
                if (! $hasTenantTrait && $this->shouldBeTenant($fqcn)) {
                    $withoutTenantTrait[] = $fqcn;
                }

                // Busca $fillable/$casts para client_id (no es infalible, pero útil)
                $hasClientIdCast = (bool) preg_match('/protected\s+\$fillable[^;]*client_id/s', $src)
                    || (bool) preg_match('/protected\s+\$casts[^;]*client_id/s', $src)
                    || (bool) preg_match('/function\s+client\(\)/', $src);

                if (! $hasClientIdCast && $this->shouldBeTenant($fqcn)) {
                    $withoutClientIdCast[] = $fqcn;
                }

            } catch (\Throwable $e) {
                // Ignora modelos problemáticos en reflexión
            }
        }

        return [
            'all' => $all,
            'without_tenant_trait' => $withoutTenantTrait,
            'without_client_id_cast' => $withoutClientIdCast,
        ];
    }

    private function shouldBeTenant(string $fqcn): bool
    {
        // Heurística simple: modelos que por naming suelen ser tenanciables
        $name = class_basename($fqcn);
        $candidates = [
            'Company', 'Branch', 'Workspace', 'Report', 'Dataset', 'Audit', 'UserSetting', 'Role', 'Permission',
            'Customer', 'ClientSetting', 'Invoice', 'Subscription', 'Capacity', 'Log', 'Event',
        ];
        foreach ($candidates as $cand) {
            if (str_contains($name, $cand)) {
                return true;
            }
        }

        // fallback: la mayoría de modelos del negocio (excepto Client y SuperAdmin-only)
        return ! in_array($name, ['Client']);
    }

    /* ===================== MIGRACIONES ===================== */

    private function inspectMigrations(): array
    {
        $dir = database_path('migrations');
        $files = File::exists($dir) ? File::allFiles($dir) : [];
        $missingClientId = [];

        foreach ($files as $f) {
            $txt = File::get($f->getRealPath());
            // Busca create o table->uuid/bigInteger('client_id')
            $createsTable = preg_match('/Schema::create\(\'([^\']+)\'/', $txt, $m) ? $m[1] : null;
            if (! $createsTable) {
                continue;
            }

            $tenancyCandidate = ! preg_match('/\bclients\b/', $createsTable); // no exigir en clients
            if ($tenancyCandidate) {
                $hasClientId = preg_match('/\$table->.*(uuid|unsignedBigInteger|foreignId)\(\s*[\'"]client_id[\'"]\s*\)/', $txt);
                if (! $hasClientId) {
                    $missingClientId[] = [
                        'table' => $createsTable,
                        'file' => $f->getRelativePathname(),
                    ];
                }
            }
        }

        return ['missing_client_id' => $missingClientId];
    }

    /* ===================== RUTAS ===================== */

    private function inspectRoutes(): array
    {
        $routes = Route::getRoutes();
        $withoutAuth = [];
        $withoutCan = [];

        foreach ($routes as $r) {
            $action = $r->getAction();
            $uri = $r->uri();
            $name = $r->getName() ?? '';
            $middlewares = Arr::wrap($action['middleware'] ?? []);

            // Ignorar rutas públicas típicas (login, password.*)
            $publicOk = preg_match('/^(login|logout|password|sanctum|telescope|horizon|_debugbar)/', $uri);

            if (! $publicOk && ! $this->hasAny($middlewares, ['auth', 'verified'])) {
                $withoutAuth[] = ['uri' => $uri, 'name' => $name, 'methods' => $r->methods(), 'middleware' => $middlewares];
            }

            // Busca can:* o policy en rutas protegidas
            $hasCan = $this->hasPrefix($middlewares, 'can:');
            if (! $publicOk && $this->hasAny($middlewares, ['auth', 'verified']) && ! $hasCan) {
                $withoutCan[] = ['uri' => $uri, 'name' => $name];
            }
        }

        return [
            'without_auth' => $withoutAuth,
            'without_can' => $withoutCan,
        ];
    }

    private function hasAny(array $list, array $needles): bool
    {
        foreach ($needles as $n) {
            foreach ($list as $m) {
                if (str_contains((string) $m, $n)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasPrefix(array $list, string $prefix): bool
    {
        foreach ($list as $m) {
            if (str_starts_with((string) $m, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /* ===================== CONTROLADORES ===================== */

    private function inspectControllers(): array
    {
        $dir = app_path('Http/Controllers');
        $files = File::exists($dir) ? File::allFiles($dir) : [];
        $withoutAuthorizeResource = [];

        foreach ($files as $f) {
            if ($f->getExtension() !== 'php') {
                continue;
            }
            $src = File::get($f->getRealPath());
            // Heurística: buscar $this->authorizeResource( o this->authorize(
            $hasAuthorize = preg_match('/->authorizeResource\(/', $src) || preg_match('/\$this->authorize\(/', $src);
            if (! $hasAuthorize) {
                $withoutAuthorizeResource[] = $f->getRelativePathname();
            }
        }

        return ['without_authorize_resource' => $withoutAuthorizeResource];
    }

    /* ===================== VISTAS ===================== */

    private function inspectViews(): array
    {
        $dir = resource_path('views');
        $files = File::exists($dir) ? File::allFiles($dir) : [];
        $suspectedCrossTenant = [];

        foreach ($files as $f) {
            $txt = File::get($f->getRealPath());
            // Heurística: rutas absolutas o includes “globales” peligrosos
            if (preg_match('/\/companies\/|\/branches\/|client_id\s*=\s*null/i', $txt)) {
                $suspectedCrossTenant[] = $f->getRelativePathname();
            }
        }

        return ['suspected_cross_tenant' => $suspectedCrossTenant];
    }

    /* ===================== TRADUCCIONES ===================== */

    private function inspectTranslations(): array
    {
        $langDir = resource_path('lang');
        $missingKeys = [];
        $foundKeys = [];

        // Buscar __() y @lang en vistas/controladores
        $scanDirs = [resource_path('views'), app_path('Http/Controllers')];
        foreach ($scanDirs as $dir) {
            if (! File::exists($dir)) {
                continue;
            }
            foreach (File::allFiles($dir) as $f) {
                $txt = File::get($f->getRealPath());
                preg_match_all('/__\([\'"]([^\'"]+)[\'"]\)/', $txt, $m1);
                preg_match_all('/@lang\([\'"]([^\'"]+)[\'"]\)/', $txt, $m2);
                $foundKeys = array_merge($foundKeys, $m1[1] ?? [], $m2[1] ?? []);
            }
        }

        $foundKeys = array_values(array_unique($foundKeys));
        $foundKeys = array_values(array_filter(
            $foundKeys,
            fn (string $key): bool => ! str_contains($key, '::') && ! str_ends_with($key, '.')
        ));

        // Cargar diccionarios existentes (es/en/pt…)
        $available = [];
        if (File::exists($langDir)) {
            foreach (File::directories($langDir) as $locDir) {
                $locale = basename($locDir);
                $available[$locale] = $this->flattenLangArray($locale);
            }
        }

        foreach ($foundKeys as $key) {
            foreach ($available as $locale => $entries) {
                if (! array_key_exists($key, $entries)) {
                    $missingKeys[] = ['key' => $key, 'locale' => $locale];
                }
            }
        }

        return ['missing_keys' => $missingKeys];
    }

    private function flattenLangArray(string $locale): array
    {
        $base = resource_path('lang/'.$locale);
        $acc = [];
        if (! File::exists($base)) {
            return $acc;
        }

        foreach (File::allFiles($base) as $f) {
            if ($f->getExtension() !== 'php') {
                continue;
            }
            $arr = @include $f->getRealPath();
            if (! is_array($arr)) {
                continue;
            }
            $this->dotify($arr, $f->getFilenameWithoutExtension(), $acc);
        }

        $json = resource_path("lang/{$locale}.json");
        if (File::exists($json)) {
            $acc += json_decode(File::get($json), true, 512, JSON_THROW_ON_ERROR);
        }

        return $acc;
    }

    private function dotify(array $arr, string $prefix, array &$acc): void
    {
        foreach ($arr as $k => $v) {
            $key = $prefix === '' ? $k : $prefix.'.'.$k;
            if (is_array($v)) {
                $acc[$key] = $v;
                $this->dotify($v, $key, $acc);
            } else {
                $acc[$key] = $v;
            }
        }
    }

    /* ===================== BI / FABRIC ===================== */

    private function inspectBiServices(): array
    {
        $appDir = app_path();
        $files = File::exists($appDir) ? File::allFiles($appDir) : [];
        $warnings = [];

        foreach ($files as $f) {
            if ($f->getExtension() !== 'php') {
                continue;
            }
            $txt = File::get($f->getRealPath());
            if (preg_match('/PowerBi|Fabric/i', $txt)) {
                // Heurísticas: falta try/catch, timeout Guzzle, logs insuficientes, no cache token
                if (! preg_match('/try\s*{/', $txt)) {
                    $warnings[] = $f->getRelativePathname().' → posible falta de manejo de errores (try/catch).';
                }
                if (preg_match('/GuzzleHttp\\\Client/', $txt) && ! preg_match('/timeout\s*=>\s*\d+/', $txt)) {
                    $warnings[] = $f->getRelativePathname().' → Guzzle sin timeout explícito.';
                }
                if (preg_match('/getAccessToken/i', $txt) && ! preg_match('/cache|Cache::remember/i', $txt)) {
                    $warnings[] = $f->getRelativePathname().' → AccessToken sin cache.';
                }
            }
        }

        return compact('warnings');
    }

    /* ===================== AUDITORÍA ===================== */

    private function inspectAudits(): array
    {
        $dir = app_path();
        $files = File::exists($dir) ? File::allFiles($dir) : [];
        $warnings = [];

        foreach ($files as $f) {
            if ($f->getExtension() !== 'php') {
                continue;
            }
            $txt = File::get($f->getRealPath());
            if (preg_match('/Audit/i', $txt)) {
                // Requeridos mínimos
                foreach (['client_id', 'user_id', 'report_uuid', 'action'] as $field) {
                    if (! preg_match('/'.$field.'/', $txt)) {
                        $warnings[] = $f->getRelativePathname().' → auditoría podría no registrar '.$field.'.';
                    }
                }
            }
        }

        return compact('warnings');
    }

    /* ===================== HELPERS ===================== */

    private function fqcnFromFile(string $path): ?string
    {
        $src = File::get($path);
        $ns = null;
        if (preg_match('/namespace\s+([^;]+);/', $src, $m)) {
            $ns = trim($m[1]);
        }
        if (preg_match('/class\s+([A-Za-z0-9_]+)/', $src, $m)) {
            $cls = trim($m[1]);

            return $ns ? $ns.'\\'.$cls : $cls;
        }

        return null;
    }

    private function prettyPrint(array $report): void
    {
        $this->info('=== PROJECT AUDIT ===');

        $this->section('MODELOS sin Tenancy');
        foreach ($report['models']['without_tenant_trait'] as $m) {
            $this->line(' - '.$m);
        }

        $this->section('MODELOS sin client_id/casts/relación');
        foreach ($report['models']['without_client_id_cast'] as $m) {
            $this->line(' - '.$m);
        }

        $this->section('MIGRACIONES sin client_id');
        foreach ($report['migrations']['missing_client_id'] as $m) {
            $this->line(" - {$m['table']} ({$m['file']})");
        }

        $this->section('RUTAS sin auth');
        foreach ($report['routes']['without_auth'] as $r) {
            $this->line(" - {$r['uri']} [".implode(',', $r['methods']).']');
        }

        $this->section('RUTAS sin can:*');
        foreach ($report['routes']['without_can'] as $r) {
            $this->line(" - {$r['uri']} ({$r['name']})");
        }

        $this->section('CONTROLADORES sin authorize/authorizeResource');
        foreach ($report['controllers']['without_authorize_resource'] as $c) {
            $this->line(' - '.$c);
        }

        $this->section('VISTAS sospechosas cross-tenant');
        foreach ($report['views']['suspected_cross_tenant'] as $v) {
            $this->line(' - '.$v);
        }

        $this->section('Traducciones faltantes');
        foreach ($report['translations']['missing_keys'] as $t) {
            $this->line(" - {$t['locale']}: {$t['key']}");
        }

        $this->section('BI Services Warnings');
        foreach ($report['bi_services']['warnings'] as $w) {
            $this->line(' - '.$w);
        }

        $this->section('Auditoría Warnings');
        foreach ($report['audits']['warnings'] as $w) {
            $this->line(' - '.$w);
        }

        $this->section('RESUMEN');
        foreach ($report['summary'] as $k => $v) {
            $this->line(sprintf(' - %-35s %5d', $k, $v));
        }
    }

    private function section(string $title): void
    {
        $this->line('');
        $this->comment("## {$title}");
    }
}
