<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
}
