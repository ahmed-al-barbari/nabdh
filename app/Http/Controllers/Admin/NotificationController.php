<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->where('data->type', 'admin_notification')->paginate();
        info($notifications);
        return [
            'notifications' => $notifications,
            'unread_count' => $notifications->whereNull('read_at')->count(),
        ];
    }
}
