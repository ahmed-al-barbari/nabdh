<?php

namespace App\Listeners;

use App\Events\UserNotification as UserNotificationEvent;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class UserNotification {
    /**
    * Create the event listener.
    */

    public function __construct() {
        //
    }

    /**
    * Handle the event.
    */

    public function handle( UserNotificationEvent $event ): void {
        $users = User::with( [ 'userNotifications' ] )->where( 'recive_notification', true )->get();
        $productPrice = $event->product->price;
        $productName = $event->product->name;
        $storeName = $event->product->store->name;

        foreach ( $users as $user ) {
            $alert = $user->userNotifications->where( 'product_id', $event->product->product_id )->first();
            if ( $alert?->status == 'active' ) {
                $type = $alert->type;
                if ( ( $type == 'gt' ) && $alert->target_price < $productPrice ) {
                    $title = "{$productName} ارتفع الى {$productPrice}₪ في {$storeName}";
                    $status = 'gt';
                    $user->notify( new \App\Notifications\UserNotification( $title, $status ) );
                    if ( !$alert->is_triggered ) {
                        $alert->update( [
                            'is_triggered' => true,
                        ] );
                    }
                }
                if ( ( $type == 'lt' ) && $alert->target_price > $productPrice ) {
                    $title = "{$productName} انخفض الى {$productPrice}₪ في {$storeName}";
                    $status = 'lt';
                    $user->notify( new \App\Notifications\UserNotification( $title, $status ) );
                    if ( !$alert->is_triggered ) {
                        $alert->update( [
                            'is_triggered' => true,
                        ] );
                    }
                }

            }
        }
        // Notification::send( $users, );
    }
}
