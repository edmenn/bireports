<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\StampEntraOnFirstLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

final class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [
            StampEntraOnFirstLogin::class,
        ],
    ];

    public function boot(): void
    {
        $observer = \App\Observers\EntityObserver::class;
        $models = array_unique(array_filter(config('audit.entities', []), 'is_string'));

        foreach ($models as $modelClass) {
            if (!class_exists($modelClass)) {
                continue;
            }

            if (!is_subclass_of($modelClass, \Illuminate\Database\Eloquent\Model::class)) {
                continue;
            }

            try {
                $modelClass::observe($observer);
            } catch (\Throwable $e) {
                // Evita que una clase problemática bloquee el arranque del proveedor.
            }
        }
    }

    protected $subscribe = [
        \App\Listeners\LogSpatiePermissionEvents::class,
    ];
}
