<?php

namespace App\Events;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOfferEvent {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
    * Create a new event instance.
    */

    public function __construct( public Product $product ) {
        //
    }
}
