<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model {
    use HasFactory;
    protected $fillable = [ 'barter_id', 'sender_id', 'receiver_id', 'message', 'read_status' ];

    public function barter() {
        return $this->belongsTo( Barter::class );
    }

    public function sender() {
        return $this->belongsTo( User::class, 'sender_id' );

    }

    public function receiver() {
        return $this->belongsTo( User::class, 'receiver_id' );
    }

}
