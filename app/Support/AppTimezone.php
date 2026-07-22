<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Configuration;

final class AppTimezone
{
    public static function current(): string
    {
        try {
            $tz = optional(Configuration::query()->first())->app_timezone_db;
            $candidate = $tz ?: (string) config('app.timezone');

            return in_array($candidate, timezone_identifiers_list(), true) ? $candidate : 'UTC';
        } catch (\Throwable $e) {
            $candidate = (string) config('app.timezone');

            return in_array($candidate, timezone_identifiers_list(), true) ? $candidate : 'UTC';
        }
    }
}
