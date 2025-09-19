<?php

namespace App\Listeners;

use App\Events\ChangeUserRoleEvent;
use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Notification;

class ChageUserRole
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ChangeUserRoleEvent $event): void
    {
        $users = User::where('role', 'admin')->get();
        $title = "تغيير دور المستخدم";
        $icon = 'role';
        $role = $event->user->role == 'merchant' ? 'تاجر' : 'مستهلك';
        $text = "قام {$event->user->name} بتغيير دوره الى {$role}";
        Notification::send($users, new AdminNotification($title, $text, $icon));
    }
}
