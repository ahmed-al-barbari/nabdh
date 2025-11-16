<?php

namespace App\Listeners;

use App\Events\ChangeUserRoleEvent;
use App\Models\User;
use App\Notifications\AdminNotification;
use Notification;

class ChangeUserRole {
    /**
    * Create the event listener.
    */


    /**
    * Handle the event.
    */

    public function handle( ChangeUserRoleEvent $event ): void {
        $users = User::where( 'role', 'admin' )->get();
        $title = 'تغيير دور المستخدم';
        $icon = 'role';
        $role = $event->user->role == 'merchant' ? 'تاجر' : 'مستهلك';
        $text = "قام {$event->user->name} بتغيير دوره إلى {$role}";
        Notification::send( $users, new AdminNotification( $title, $text, $icon ) );
    }
}
