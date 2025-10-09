<?php

namespace App\Listeners;

use App\Events\NewOfferEvent;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class NewOffer {
    /**
    * Create the event listener.
    */

    public function __construct() {
        //
    }

    /**
    * Handle the event.
    */

    public function handle( NewOfferEvent $event ): void {
        $users = User::with( [ 'userNotifications' ] )->where( 'recive_notification', true )->get();
        $product = $event->product;
        $store = $event->product->store;
        $title = "عرض جديد: خصم على {$product->name} في {$store->name}";
        $status = 'offer';

        Notification::send( $users, new \App\Notifications\UserNotification( $title, $status ) );
    }
}
