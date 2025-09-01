<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Enums\ApiMessage;

class NotificationController extends Controller
{

    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())->get();

        return response()->json([
            'message' => ApiMessage::NOTIFICATION_LIST->value,
            'notifications' => $notifications
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $user = Auth::user();

        $notification = Notification::create([
            'user_id' => $user->id,
            'title'   => $validated['title'],
            'message' => $validated['message'],
            'method'  => $user->notification_method, // ناخدها من البروفايل
        ]);

        return response()->json([
            'message'      => ApiMessage::NOTIFICATION_CREATED->value,
            'notification' => $notification
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'target_price' => 'nullable|numeric|min:0'
        ]);

        $notification->update($validated);

        return response()->json([
            'message'      => ApiMessage::NOTIFICATION_UPDATED->value,
            'notification' => $notification
        ]);
    }

    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->delete();

        return response()->json([
            'message' => ApiMessage::NOTIFICATION_DELETED->value
        ]);
    }
}
