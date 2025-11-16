<?php

namespace App\Listeners;

use App\Events\UserNotification as UserNotificationEvent;
use App\Models\User;

class UserNotification {
    /**
    * Create the event listener.
    */


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
            // Check if alert is active AND not already triggered to prevent duplicates
            if ( $alert?->status == 'active' && !$alert->is_triggered ) {
                $type = $alert->type;
                if ( ( $type == 'gt' ) && $alert->target_price < $productPrice ) {
                    // Update alert status immediately to prevent duplicate processing
                    $alert->update( [
                        'is_triggered' => true,
                        'status' => 'inactive',
                    ] );
                    $title = "{$productName} ارتفع إلى {$productPrice}₪ في {$storeName}";
                    $status = 'gt';
                    $user->notify( new \App\Notifications\UserNotification( $title, $status, $alert->id ) );
                }
                if ( ( $type == 'lt' ) && $alert->target_price > $productPrice ) {
                    // Update alert status immediately to prevent duplicate processing
                    $alert->update( [
                        'is_triggered' => true,
                        'status' => 'inactive',
                    ] );
                    $title = "{$productName} انخفض إلى {$productPrice}₪ في {$storeName}";
                    $status = 'lt';
                    $user->notify( new \App\Notifications\UserNotification( $title, $status, $alert->id ) );
                }

            }
        }
    }
}
