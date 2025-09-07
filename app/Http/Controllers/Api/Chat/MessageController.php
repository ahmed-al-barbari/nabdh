<?php

// app/Http/Controllers/Api/Chat/MessageController.php
namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\MessageConversation;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function markAsRead($id)
    {
        $message = MessageConversation::findOrFail($id);

        // ما ينفعش غير صاحب المحادثة يقرأ
        if (
            $message->conversation->user_one_id !== Auth::id() &&
            $message->conversation->user_two_id !== Auth::id()
        ) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json([
            'message' => 'Message marked as read',
            'data' => $message
        ]);
    }

    public function sendMessage(Request $request)
    {
        $data = $request->validate([
            'receiver_id' => 'required|exists:users,id|different:' . Auth::id(),
            'content'     => 'required|string',
        ]);

        $ids = [Auth::id(), $data['receiver_id']];
        sort($ids);

        // إما يرجع المحادثة أو ينشئ جديدة
        $conversation = Conversation::firstOrCreate([
            'user_one_id' => $ids[0],
            'user_two_id' => $ids[1],
        ]);

        // إنشاء الرسالة
        $message = $conversation->messages()->create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $data['receiver_id'],
            'content'     => $data['content'],
            'is_read'     => false,
        ]);

        return response()->json([
            'message'      => 'Message sent successfully',
            'conversation' => $conversation,
            'data'         => $message,
        ], 201);
    }
}
