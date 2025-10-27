<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsNotBlocked
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && (bool) ($user->is_blocked ?? false)) {
            Auth::logout();

            try {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            } catch (\Throwable $e) {
                // Ignore session handler failures
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('common.account_blocked_short'),
                ], 403);
            }

            return redirect()->route('login')->with('error', __('common.account_blocked'));
        }

        return $next($request);
    }
}
