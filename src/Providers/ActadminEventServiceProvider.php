<?php

namespace ACT\Actadmin\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use ACT\Actadmin\Events;
use ACT\Actadmin\Listeners;

class ActadminEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Events\BreadAdded::class => [
            Listeners\AddBreadMenuItem::class,
            Listeners\AddBreadPermission::class,
        ],
        Events\BreadDeleted::class => [
            Listeners\DeleteBreadMenuItem::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
