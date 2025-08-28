<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Busca el idioma en la sesión, si no está usa el default de config/app.php
        $locale = session('locale', config('app.locale'));

        // Forzamos el idioma en la app
        App::setLocale($locale);

        return $next($request);
    }
}
