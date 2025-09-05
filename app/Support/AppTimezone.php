<?php

declare(strict_types=1);

namespace App\Support;

final class AppTimezone
{
    public static function current(): string
    {
        try {
            $tz = optional(\App\Models\Configuracion::query()->first())->app_timezone_db;
            return $tz ?: (string) config('app.timezone');
        } catch (\Throwable $e) {
            return (string) config('app.timezone');
        }
    }
}

