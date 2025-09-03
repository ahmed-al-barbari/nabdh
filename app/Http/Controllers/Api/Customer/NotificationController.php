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
        $notifications = Notification::with(['product:id,name'])->where('user_id', Auth::id())->get();

        return response()->json([
            'message' => ApiMessage::NOTIFICATION_LIST->value,
            'notifications' => $notifications
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // 'title' => 'required|string|max:255',
            'product_id' => 'required|int|exists:main_products,id',
            'type' => 'required|string|in:lt,gt',
            'target_price' => 'required|int',
        ]);

        $user = Auth::user();

        $notification = Notification::create([...$validated, 'user_id' => $user->id]);

        return response()->json([
            'message' => ApiMessage::NOTIFICATION_CREATED->value,
            'notification' => $notification
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'status' => 'nullable|string|in:active,inactive'
        ]);

        $notification->update($validated);

        return response()->json([
            'message' => ApiMessage::NOTIFICATION_UPDATED->value,
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

    public function changeMethodStauts(Request $request)
    {
        $request->validate([
            'status' => 'required|boolean',
            'notification_method' => 'required|in:email,sms,whats',
        ]);

        $user = Auth::user();

        $methods = $user->notification_methods ?? [];

        $methods[$request->notification_method] = $request->status;

        $user->notification_methods = $methods;
        $user->save();

        return response()->json(['message' => 'Notification method updated successfully']);

    }
}
