<?php

// app/Http/Controllers/Api/Chat/ConversationController.php
namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    // إنشاء أو إرجاع محادثة قائمة بين المستخدمين
    public function start(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id|different:' . Auth::id(),
        ]);

        $ids = [Auth::id(), $data['user_id']];
        sort($ids);

        $conversation = Conversation::firstOrCreate([
            'user_one_id' => $ids[0],
            'user_two_id' => $ids[1],
        ]);

        return response()->json([
            'message' => 'Conversation ready',
            'conversation' => $conversation
        ], 201);
    }
}
