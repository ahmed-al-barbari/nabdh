<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageConversation extends Model {
    public $table = 'messages_conversations';
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'is_read',
        'read_at'
    ];

    public function conversation() {
        return $this->belongsTo( Conversation::class );
    }

    public function sender() {
        return $this->belongsTo( User::class, 'sender_id' );
    }
}
