<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Decide the redirect target for unauthenticated web requests.
     * APIs typically return 401 responses instead of redirecting.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            // Update 'login' if your application uses a custom login route name.
            return route('login');
        }
        return null;
    }
}
