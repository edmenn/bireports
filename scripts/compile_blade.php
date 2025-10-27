<?php
// Helper to compile a single Blade file and dump compiled PHP to stdout.
// Usage: php scripts/compile_blade.php resources/views/configurations/theme.blade.php

declare(strict_types=1);

chdir(dirname(__DIR__));
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Ensure view services are registered when running outside HTTP lifecycle
// Register needed providers when running standalone
if (!$app->bound('files')) {
    $app->register(Illuminate\Filesystem\FilesystemServiceProvider::class);
}
if (!$app->bound('events')) {
    $app->register(Illuminate\Events\EventServiceProvider::class);
}
if (!$app->bound('view')) {
    $app->register(Illuminate\View\ViewServiceProvider::class);
}

// Resolve the Blade compiler via the view engine resolver
/** @var Illuminate\View\Factory $viewFactory */
$viewFactory = $app->make(Illuminate\View\Factory::class);
$engine = $viewFactory->getEngineResolver()->resolve('blade');
if (!method_exists($engine, 'getCompiler')) {
    fwrite(STDERR, "Unexpected engine without getCompiler()\n");
    exit(1);
}
/** @var Illuminate\View\Compilers\BladeCompiler $blade */
$blade = $engine->getCompiler();

$path = $argv[1] ?? null;
if (!$path || !is_file($path)) {
    fwrite(STDERR, "Usage: php scripts/compile_blade.php <path-to-blade>\n");
    exit(1);
}

$src = file_get_contents($path);
$compiled = $blade->compileString($src);

echo "==== BEGIN COMPILED ====\n";
echo $compiled;
echo "\n==== END COMPILED ====\n";
