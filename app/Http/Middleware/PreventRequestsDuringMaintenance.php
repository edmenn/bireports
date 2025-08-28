<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * URIs que deben estar accesibles aún en mantenimiento.
     * Deja vacío salvo que necesites exponer algo (webhooks, healthchecks, etc.).
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
