<?php

declare(strict_types=1);

use App\Http\Middleware\AutoCapacityActivity;
use App\Http\Middleware\EnsureUserIsNotBlocked;
use App\Http\Middleware\SetAdminlteTheme;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackAudit;
use App\Providers\MenuServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('TRUSTED_PROXIES', '127.0.0.1'))
        ))));

        // Aliases de Spatie Permission (roles/permisos)
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        $middleware->appendToGroup('web', [
            EnsureUserIsNotBlocked::class,
            SetLocale::class,
            SetAdminlteTheme::class,
            TrackAudit::class,
            AutoCapacityActivity::class,
        ]);
    })
    ->withProviders([
        MenuServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withExceptions(function (Exceptions $exceptions): void {
        // Usa el manejador por defecto de Laravel
    })
    ->create();
