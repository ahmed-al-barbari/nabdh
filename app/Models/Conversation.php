<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['user_one_id', 'user_two_id'];

    public function message_conversation()
    {
        return $this->hasMany(MessageConversation::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_user'); // اختياري إن أردت Pivot
    }

    public function includesUser($userId): bool
    {
        return in_array($userId, [$this->user_one_id, $this->user_two_id]);
    }
}
