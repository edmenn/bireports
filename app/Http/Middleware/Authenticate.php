<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * A dónde redirigir si la request no está autenticada (rutas web).
     * Para API normalmente devolvés 401 y no redirigís.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            // Cambia 'login' por el nombre de tu ruta de login si corresponde.
            return route('login');
        }
        return null;
    }
}
