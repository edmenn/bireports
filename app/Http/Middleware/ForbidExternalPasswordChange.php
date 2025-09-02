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
        // 1) Detectar si en el payload hay CUALQUIER campo relacionado a "password"
        $hasPasswordFields = false;
        foreach ($request->all() as $key => $value) {
            if (is_string($key) && stripos($key, 'password') !== false) {
                $hasPasswordFields = true;
                break;
            }
        }

        // Si no hay campos de password, seguimos
        if (!$hasPasswordFields) {
            return $next($request);
        }

        // 2) Resolver el "usuario objetivo" de la operación
        //    - Si es /profile => usuario autenticado
        //    - Si es ABM (usuarios.update) => parámetro de ruta 'usuario' (o 'user'/'id' como fallback)
        $target = null;

        // a) intentar por ruta (ABM)
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

        // b) si no, el propio usuario autenticado (/profile)
        if (!$target) {
            $target = $request->user();
        }

        // 3) Si el target es Entra, bloquear
        if ($target && $target->auth_provider === 'entra') {
            // Limpiamos los campos para no reinyectar passwords en sesión
            $cleaned = $request->except(array_keys($request->all()));
            return redirect()->back()
                ->withInput($cleaned)
                ->with('error', 'La contraseña de este usuario se gestiona en Microsoft Entra. No puedes cambiarla aquí.');
        }

        return $next($request);
    }
}
