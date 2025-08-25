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
            'product_id'   => 'required|exists:products,id',
            'target_price' => 'required|numeric|min:0',
            'method'       => 'required|in:sms,whatsapp,email',
        ]);

        $validated['user_id'] = Auth::id();

        $notification = Notification::create($validated);

        return response()->json([
            'message' => ApiMessage::NOTIFICATION_CREATED->value,
            'notification' => $notification
        ]);
    }

    public function update(Request $request, $id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'target_price' => 'nullable|numeric|min:0',
            'method'       => 'nullable|in:sms,whatsapp,email',
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
}
