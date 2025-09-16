<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    // إرجاع جميع محادثات المستخدم
    public function index()
    {
        $userId = Auth::id();

        $conversations = Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with([
                'userOne',
                'userTwo',
                'messages_conversation',
                'messages_conversationUnread' => function ($q) use ($userId) {
                    $q->where('is_read', false)
                        ->where('sender_id', '!=', $userId);
                }
            ])
            ->latest()
            ->get()
            ->map(function ($conversation) use ($userId) {
                $lastMessage = $conversation->messages_conversation->first();
                return [
                    'id' => $conversation->id,
                    'user_one' => $conversation->userOne,
                    'user_two' => $conversation->userTwo,
                    'last_message_body' => $lastMessage?->body,
                    'last_message_date' => $lastMessage?->created_at,
                    'last_message_sender_id' => $lastMessage?->sender_id,
                    'unread_count' => $conversation->messages_conversationUnread->count(),
                ];
            });

        $conversationsWithUnread = $conversations->filter(fn($c) => $c['unread_count'] > 0)->count();

        return response()->json([
            'message' => 'Conversations fetched successfully',
            'conversations' => $conversations,
            'conversations_with_unread_count' => $conversationsWithUnread,
        ]);
    }
}
