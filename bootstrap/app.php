<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware): void {

        
        // Si querés hacer el archivo “a prueba de versión”, podés fallback al plural:
        // Compatibilidad: v6 usa \Middleware (singular), v5 usaba \Middlewares (plural)
        $roleMw               = \Spatie\Permission\Middleware\RoleMiddleware::class;
        $permMw               = \Spatie\Permission\Middleware\PermissionMiddleware::class;
        $roleOrPermMw         = \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class;

        $middleware->alias([
            'role'               => $roleMw,
            'permission'         => $permMw,
            'role_or_permission' => $roleOrPermMw,
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocale::class);
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withExceptions(function (Exceptions $exceptions): void {})
    ->create();