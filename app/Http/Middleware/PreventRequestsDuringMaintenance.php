<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * URIs that should remain reachable during maintenance mode.
     * Leave empty unless you must expose something (webhooks, health checks, etc.).
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
