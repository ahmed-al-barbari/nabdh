<?php

namespace App\Listeners;

use App\Events\NewStoreEvent;
use App\Models\User;
use App\Notifications\AdminNotification;
use Notification;

class NewStore {
    /**
    * Create the event listener.
    */


    /**
    * Handle the event.
    */

    public function handle( NewStoreEvent $event ): void {
        $users = User::where( 'role', 'admin' )->get();
        $title = 'متجر جديد';
        $icon = 'store';
        $text = "قام {$event->store->user->name} بانشاء المتجر {$event->store->name}";
        Notification::send( $users, new AdminNotification( $title, $text, $icon ) );
    }
}
