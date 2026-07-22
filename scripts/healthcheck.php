<?php

declare(strict_types=1);

try {
    require dirname(__DIR__).'/vendor/autoload.php';
    $app = require dirname(__DIR__).'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    Illuminate\Support\Facades\DB::select('select 1');
    exit(0);
} catch (Throwable) {
    fwrite(STDERR, "Application health check failed.\n");
    exit(1);
}
