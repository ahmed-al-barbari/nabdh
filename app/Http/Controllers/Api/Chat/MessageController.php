<?php

namespace App\Http\Controllers\Api\Chat;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index($conversationId)
    {
        $conv = Conversation::findOrFail($conversationId);
        abort_unless($conv->includesUser(Auth::id()), 403, 'Forbidden');

        $messages = $conv->messages()->with('sender:id,name')->orderBy('id', 'asc')->paginate(30);

        return response()->json([
            'message'  => 'Messages fetched',
            'messages' => $messages
        ]);
    }

    public function store(Request $request, $conversationId)
    {
        $conv = Conversation::findOrFail($conversationId);
        abort_unless($conv->includesUser(Auth::id()), 403, 'Forbidden');

        $data = $request->validate([
            'body' => 'required|string|max:5000'
        ]);

        $message = Message::create([
            'conversation_id' => $conv->id,
            'sender_id'       => Auth::id(),
            'body'            => $data['body'],
        ]);

        // بث الحدث إلى القناة الخاصة بالمحادثة
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => 'Message sent',
            'data'    => $message
        ], 201);
    }
}
