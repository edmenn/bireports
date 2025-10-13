<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ForbidExternalPasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1) Detect if the payload includes any field related to a password
        $hasPasswordFields = false;
        foreach ($request->all() as $key => $value) {
            if (is_string($key) && stripos($key, 'password') !== false) {
                $hasPasswordFields = true;
                break;
            }
        }

        // Skip middleware when no password-like fields were provided
        if (!$hasPasswordFields) {
            return $next($request);
        }

        // 2) Resolve the targeted user for the operation
        //    - For /profile => use the authenticated user
        //    - For CRUD routes (users.update) => read the route parameter ('usuario' legacy, or 'user'/'id' fallback)
        $target = null;

        // a) Try the route parameters first (CRUD flows)
        $route = $request->route();
        if ($route) {
            foreach (['usuario', 'user', 'id'] as $param) {
                $val = $route->parameter($param);
                if ($val) {
                    $found = User::query()->find($val);
                    if ($found) {
                        $target = $found;
                        break;
                    }
                }
            }
        }

        // b) Otherwise fallback to the authenticated user (/profile)
        if (!$target) {
            $target = $request->user();
        }

        // 3) Block when the target user is managed by Entra
        if ($target && $target->auth_provider === 'entra') {
            // Clear the payload to avoid re-injecting password values in the session
            $cleaned = $request->except(array_keys($request->all()));
            return redirect()->back()
                ->withInput($cleaned)
                ->with('error', __('users.password.managed_by_entra'));
        }

        return $next($request);
    }
}
