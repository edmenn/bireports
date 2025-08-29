<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Excepciones que no se reportan.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        // agrega aquí si quieres omitir reportes de algunas excepciones
    ];

    /**
     * Inputs que nunca se devuelven en flashes de validación.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Registra callbacks de manejo.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Aquí podrías enviar a logs externos/telemetría si quieres.
        });
    }

    /**
     * Renderiza la excepción en una respuesta HTTP.
     */
    public function render($request, Throwable $e)
    {
        // Para APIs / peticiones que esperan JSON, mantenemos comportamiento estándar.
        if ($request->expectsJson()) {
            return parent::render($request, $e);
        }

        // Deja que Laravel maneje normalmente las validaciones.
        if ($e instanceof ValidationException) {
            return parent::render($request, $e);
        }

        // Si no existe nuestra vista amigable, hacemos fallback al comportamiento por defecto.
        if (! view()->exists('errors.friendly')) {
            return parent::render($request, $e);
        }

        // Construye datos para la vista amigable
        $status     = $this->httpStatusCode($e);
        $code       = $e->getCode() ?: $status;
        $shortMsg   = $this->shortMessage($e, $status);
        $debug      = $this->buildDebugBlock($e);

        return response()->view('errors.friendly', [
            'status'     => $status,
            'code'       => $code,
            'message'    => $shortMsg,
            'debug'      => $debug,
            'back_url'   => url()->previous() ?: route('dashboards.index'),
        ], $status);
    }

    /**
     * Determina el HTTP status desde la excepción.
     */
    protected function httpStatusCode(Throwable $e): int
    {
        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }
        if ($e instanceof AuthenticationException) {
            return 401;
        }
        return 500;
    }

    /**
     * Mensaje corto (seguro). Si APP_DEBUG=true, incluye el mensaje original.
     */
    protected function shortMessage(Throwable $e, int $status): string
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

    /**
     * Bloque técnico colapsable para <details> (escapado).
     */
    protected function buildDebugBlock(Throwable $e): string
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
