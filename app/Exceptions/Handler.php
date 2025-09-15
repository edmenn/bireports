<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Route;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Excepciones que no se reportan.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

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
            // Puedes integrar logging/telemetría aquí si lo necesitas.
        });
    }

    /**
     * Personaliza la respuesta para ciertos errores.
     */
    public function render($request, Throwable $e)
    {
        // Redirigir al login cuando la sesión expira (CSRF TokenMismatch)
        if ($e instanceof TokenMismatchException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesión expirada.'], 419);
            }
            $loginUrl = Route::has('login') ? route('login') : url('/login');
            return redirect()->to($loginUrl)
                ->with('status', 'Tu sesión expiró. Vuelve a iniciar sesión.');
        }

        // Manejar explícitamente respuestas HTTP 419
        if ($e instanceof HttpException && $e->getStatusCode() === 419) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesión expirada.'], 419);
            }
            $loginUrl = Route::has('login') ? route('login') : url('/login');
            return redirect()->to($loginUrl)
                ->with('status', 'Tu sesión expiró. Vuelve a iniciar sesión.');
        }

        return parent::render($request, $e);
    }
}
