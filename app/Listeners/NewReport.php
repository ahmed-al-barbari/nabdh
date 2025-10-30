<?php

namespace App\Listeners;

use App\Events\NewReportEvent;
use App\Models\User;
use App\Notifications\AdminNotification;
use Notification;

class NewReport {
    /**
    * Create the event listener.
    */


    /**
    * Handle the event.
    */

    public function handle( NewReportEvent $event ): void {
        $users = User::where( 'role', 'admin' )->get();
        $title = 'ابلاغ جديد';
        $icon = 'report';
        $text = "قام {$event->user->name} بالابلاغ عن المنتج {$event->product->name}";
        Notification::send( $users, new AdminNotification( $title, $text, $icon ) );
    }
}
