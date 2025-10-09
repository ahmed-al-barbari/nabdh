<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model {
    protected $fillable = [ 'user_one_id', 'user_two_id' ];

    public function userOne() {
        return $this->belongsTo( User::class, 'user_one_id' );
    }

    public function userTwo() {
        return $this->belongsTo( User::class, 'user_two_id' );
    }

    public function messages_conversation() {
        return $this->hasMany( MessageConversation::class );
    }

    public function messages_conversationUnread() {
        return $this->hasMany( MessageConversation::class, 'conversation_id' )
        ->where( 'is_read', false );
    }

    public function scopeBetween( $query, $user1, $user2 ) {
        $ids = [ $user1, $user2 ];
        sort( $ids );

        return $query->where( 'user_one_id', $ids[ 0 ] )
        ->where( 'user_two_id', $ids[ 1 ] );
    }
}
