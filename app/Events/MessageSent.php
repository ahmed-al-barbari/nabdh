<?php
// app/Events/MessageSent.php
namespace App\Events;

use App\Models\MessageConversation;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // لحظي
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public MessageConversation $message)
    {
        $message->load('sender');
    }

    public function broadcastOn()
    {
        return new PrivateChannel("private-conversation.{$this->message->conversation_id}");
    }

    public function broadcastAs()
    {
        return "MessageSent";
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'conversationId' => $this->message->conversation_id,
            'senderId' => $this->message->sender_id,
            'body' => $this->message->body,
            'createdAt' => $this->message->created_at->toISOString(),
        ];
    }
}
