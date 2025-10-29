<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Auth;

class NotificationController extends Controller {
    public function index() {
        $notifications = Auth::user()->notifications()->where( 'data->type', 'admin_notification' )->paginate();
        info( $notifications );
        return [
            'notifications' => $notifications,
            'unread_count' => $notifications->whereNull( 'read_at' )->count(),
        ];
    }
}
