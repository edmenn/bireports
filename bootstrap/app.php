<?php

declare(strict_types=1);

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Str; // Para error_id
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
//use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    // ============================
    // MIDDLEWARE / ALIASES
    // ============================
    ->withMiddleware(function (Middleware $middleware): void {
        // Aliases de Spatie Permission (roles/permisos)
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Tu middleware de localización en el grupo web
        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocale::class);
        // (Agrega aquí otros append/prependToGroup si los usas)
    })

    // ============================
    // RUTAS
    // ============================
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',      // incluye API si la usas
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    // ============================
    // EXCEPCIONES (RENDERER GLOBAL)
    // ============================
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {
            // 1) APIs / JSON → deja manejo por defecto
            if ($request->expectsJson()) {
                return null; // null = Laravel usa su renderer estándar
            }

            // 2) Validaciones → flujo normal (redirect con errores)
            if ($e instanceof ValidationException) {
                return null;
            }

            // 3) Usa página amigable solo si FRIENDLY_ERRORS=true o si es producción
            $enableFriendly = filter_var(env('FRIENDLY_ERRORS', app()->isProduction()), FILTER_VALIDATE_BOOL);
            if (!$enableFriendly) {
                return null; // en local puedes ver Ignition
            }

            // 4) Status HTTP y mensaje seguro
            $status = 500;
            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
            } elseif ($e instanceof AuthenticationException) {
                $status = 401;
            }
            $code = $e->getCode() ?: $status;

            $map = [
                401 => 'No autorizado. Inicia sesión para continuar.',
                403 => 'Acceso denegado.',
                404 => 'Recurso no encontrado.',
                419 => 'La sesión ha caducado. Recarga la página.',
                429 => 'Demasiadas solicitudes. Intenta de nuevo.',
                500 => 'Ocurrió un error interno en el servidor.',
                503 => 'Servicio temporalmente no disponible.',
            ];

            $message = config('app.debug')
                ? sprintf('%s (HTTP %d)', trim($e->getMessage() ?: ($map[$status] ?? 'Error')), $status)
                : ($map[$status] ?? 'Se produjo un error. Intenta nuevamente.');

            // 5) error_id para correlación
            $errorId = (string) Str::uuid();

            // 6) Log sin usar facade Log (evita “Undefined type Log”)
            logger()->error('Unhandled exception (friendly-render)', [
                'error_id' => $errorId,
                'status'   => $status,
                'code'     => $e->getCode(),
                'message'  => $e->getMessage(),
                'file'     => $e->getFile() . ':' . $e->getLine(),
                'url'      => $request->fullUrl(),
                'trace'    => $e->getTrace(), // array
            ]);

            // 7) Bloque técnico colapsable (texto escapado para vista)
            $debug = e(implode("\n", [
                get_class($e) . ': ' . $e->getMessage(),
                'Code: ' . (string) ($e->getCode() ?: '0'),
                'File: ' . $e->getFile() . ':' . $e->getLine(),
                '',
                'Trace:',
                $e->getTraceAsString(),
                '',
                'Error ID: ' . $errorId,
            ]));

            // 8) Intentar vista amigable autónoma (look AdminLTE; no depende del layout)
            try {
                if (view()->exists('errors.friendly')) {
                    return response()->view('errors.friendly', [
                        'status'   => $status,
                        'code'     => $code,
                        'message'  => $message,
                        'debug'    => $debug,
                        // Evitamos route() aquí por si las rutas no están disponibles:
                        'back_url' => url()->previous() ?: url('/'),
                    ], $status);
                }
            } catch (Throwable $renderError) {
                // Si fallara la vista (paths de assets, etc.), usamos fallback mínimo
            }

            // 9) Fallback HTML mínimo (a prueba de fallos)
            $html = "<!doctype html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1'>
                <title>Error {$status}</title>
                <style>
                    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;padding:2rem;background:#f8fafc;color:#111}
                    pre{white-space:pre-wrap;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:.75rem;max-height:55vh;overflow:auto}
                    .muted{color:#64748b}
                </style></head><body>
                <h1>Error (HTTP {$status})</h1>
                <p>{$message}</p>
                <p class='muted'>Error ID: {$errorId}</p>
                <pre>{$debug}</pre>
            </body></html>";

            return response($html, $status);
        });
    })
    ->create();
