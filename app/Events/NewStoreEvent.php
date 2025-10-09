<?php

namespace App\Events;

use App\Models\Store;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewStoreEvent {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
    * Create a new event instance.
    */

    public function __construct( public Store $store ) {
        //
    }
}
