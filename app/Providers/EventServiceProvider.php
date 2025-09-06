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
        // Observers de auditoría para entidades clave
        \App\Models\User::observe(\App\Observers\EntityObserver::class);
        if (class_exists(\App\Models\Group::class)) {
            \App\Models\Group::observe(\App\Observers\EntityObserver::class);
        }
        if (class_exists(\App\Models\Folder::class)) {
            \App\Models\Folder::observe(\App\Observers\EntityObserver::class);
        }
        // Modelos de Spatie Permission
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            \Spatie\Permission\Models\Role::observe(\App\Observers\EntityObserver::class);
        }
        if (class_exists(\Spatie\Permission\Models\Permission::class)) {
            \Spatie\Permission\Models\Permission::observe(\App\Observers\EntityObserver::class);
        }
    }

    protected $subscribe = [
        \App\Listeners\LogSpatiePermissionEvents::class,
    ];
}
