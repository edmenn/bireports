<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Si el usuario ya está autenticado y visita rutas para invitados (login, registro),
     * lo redirigimos a home (ajusta según tu portal).
     */
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = $guards ?: [null];

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect()->route('home'); // ajusta a tu ruta inicial
            }
        }

        return $next($request);
    }
}
