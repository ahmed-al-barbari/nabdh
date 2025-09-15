<?php

// app/Http/Controllers/Api/Chat/MessageController.php
namespace App\Http\Controllers\Api\Chat;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\MessageConversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MessageController extends Controller
{

    public function index(User $user)
    {
        $auth = Auth::user();

        $conversation = Conversation::where(function ($q) use ($auth, $user) {
            $q->where('user_one_id', $auth->id)
                ->where('user_two_id', $user->id);
        })->orWhere(function ($q) use ($auth, $user) {
            $q->where('user_one_id', $user->id)
                ->where('user_two_id', $auth->id);
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => $auth->id,
                'user_two_id' => $user->id,
            ]);
        }
        return $this->conversationMessages($conversation);

    }

    public function conversationMessages(Conversation $conversation)
    {
        $auth = Auth::user();
        $conversation->messages_conversation()->where('sender_id', '<>', $auth->id)->update([
            'read_at' => now(),
            'is_read' => true
        ]);

        return response()->json([
            'conversation_id' => $conversation->id,
            'messages' => $conversation->messages_conversation()->latest()->paginate(20),
            'current_user_id' => $auth->id,
        ]);
    }


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
            'content' => 'required|string',
        ]);

        $ids = [Auth::id(), $data['receiver_id']];
        sort($ids);

        // إما يرجع المحادثة أو ينشئ جديدة
        $conversation = Conversation::where([
            'user_one_id' => $ids[0],
            'user_two_id' => $ids[1],
        ])->orWhere([
                    'user_one_id' => $ids[1],
                    'user_two_id' => $ids[0],
                ])->first();
        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => $ids[0],
                'user_two_id' => $ids[1],
            ]);
        }

        // إنشاء الرسالة
        $message = $conversation->messages_conversation()->create([
            'sender_id' => Auth::id(),
            'receiver_id' => $data['receiver_id'],
            'body' => $data['content'],
            'is_read' => false,
        ]);

        event(new MessageSent($message));

        return response()->json([
            'message' => 'Message sent successfully',
            'conversation' => $conversation,
            'data' => $message,
        ], 201);
    }

    // public function getMessageResponse(Conversation $conversation)
    // {

    // }
}
