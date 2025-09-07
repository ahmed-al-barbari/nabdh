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
            ->with(['userOne', 'userTwo']) // لو عندك علاقات معرفة بالمودل
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Conversations fetched successfully',
            'conversations' => $conversations
        ]);
    }
}
