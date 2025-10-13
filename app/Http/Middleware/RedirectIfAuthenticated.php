<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Redirect authenticated users away from guest-only routes (login, register).
     * Update the destination according to your application's landing page.
     */
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = $guards ?: [null];

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect()->route('home'); // adjust if your landing route differs
            }
        }

        return $next($request);
    }
}
