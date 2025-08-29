<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class FriendlyErrorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (Throwable $e) {
            // Para peticiones que esperan JSON (APIs/AJAX), no intervenimos.
            if ($request->expectsJson()) {
                throw $e;
            }

            // Si la vista amigable no existe, deja que Laravel muestre lo suyo.
            if (! view()->exists('errors.friendly')) {
                throw $e;
            }

            // Validaciones: se manejan con redirección + errores en sesión.
            if ($e instanceof ValidationException) {
                throw $e;
            }

            // Construir datos para la vista
            $status = self::statusFrom($e);
            $code   = $e->getCode() ?: $status;
            $msg    = self::shortMessage($e, $status);
            $debug  = self::debugBlock($e);

            return response()->view('errors.friendly', [
                'status'   => $status,
                'code'     => $code,
                'message'  => $msg,
                'debug'    => $debug,
                'back_url' => url()->previous() ?: route('dashboards.index'),
            ], $status);
        }
    }

    private static function statusFrom(Throwable $e): int
    {
        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }
        if ($e instanceof AuthenticationException) {
            return 401;
        }
        return 500;
    }

    private static function shortMessage(Throwable $e, int $status): string
    {
        $map = [
            401 => 'No autorizado. Inicia sesión para continuar.',
            403 => 'Acceso denegado.',
            404 => 'Recurso no encontrado.',
            419 => 'La sesión ha caducado. Recarga la página.',
            429 => 'Demasiadas solicitudes. Intenta de nuevo en unos segundos.',
            500 => 'Ocurrió un error interno en el servidor.',
            503 => 'Servicio temporalmente no disponible.',
        ];

        if (config('app.debug')) {
            $base = trim($e->getMessage() ?: ($map[$status] ?? 'Error'));
            return sprintf('%s (HTTP %d)', $base, $status);
        }

        return $map[$status] ?? 'Se produjo un error. Intenta nuevamente.';
    }

    private static function debugBlock(Throwable $e): string
    {
        $lines = [
            get_class($e) . ': ' . $e->getMessage(),
            'Code: ' . (string) ($e->getCode() ?: '0'),
            'File: ' . $e->getFile() . ':' . $e->getLine(),
            '',
            'Trace:',
            $e->getTraceAsString(),
        ];
        return e(implode("\n", $lines));
    }
}
