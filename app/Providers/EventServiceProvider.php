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
        //
    }
}
