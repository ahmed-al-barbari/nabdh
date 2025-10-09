<?php

namespace App\Listeners;

use App\Events\JoinNewUserEvent;
use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class JoinNewUser {
    /**
    * Create the event listener.
    */

    public function __construct() {
        //
    }

    /**
    * Handle the event.
    */

    public function handle( JoinNewUserEvent $event ): void {
        $users = User::where( 'role', 'admin' )->get();
        $title = 'مستخدم جديد';
        $icon = 'user';
        $text = "انشأ {$event->user->name} حساب في الموقع.";
        Notification::send( $users, new AdminNotification( $title, $text, $icon ) );
    }
}
