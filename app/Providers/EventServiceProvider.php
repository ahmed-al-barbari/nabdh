<?php

namespace App\Providers;

use App\Events\ChangeUserRoleEvent;
use App\Events\JoinNewUserEvent;
use App\Events\NewOfferEvent;
use App\Events\NewReportEvent;
use App\Events\NewStoreEvent;
use App\Events\UserNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider {
    /**
    * The event to listener mappings for the application.
    *
    * @var array<class-string, array<int, class-string>>
    */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserNotification::class => [
            \App\Listeners\UserNotification::class,
        ],
        NewReportEvent::class => [
            \App\Listeners\NewReport::class,
        ],
        JoinNewUserEvent::class => [
            \App\Listeners\JoinNewUser::class,
        ],
        NewStoreEvent::class => [
            \App\Listeners\NewStore::class,
        ],
        ChangeUserRoleEvent::class => [
            \App\Listeners\ChageUserRole::class,
        ],
        NewOfferEvent::class => [
            \App\Listeners\NewOffer::class,
        ],
    ];

    /**
    * Register any events for your application.
    */

    public function boot(): void {
        //
    }

    /**
    * Determine if events and listeners should be automatically discovered.
    */

    public function shouldDiscoverEvents(): bool {
        return false;
    }
}
