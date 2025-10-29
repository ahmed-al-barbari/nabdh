<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
// مهم عشان البث
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class PriceUpdated implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $productId;
    public $newPrice;

    /**
    * Create a new event instance.
    */

    public function __construct( $productId, $newPrice ) {
        $this->productId = $productId;
        $this->newPrice = $newPrice;
    }

    /**
    * القناة اللي هيتم البث عليها
    */

    public function broadcastOn() {
        return new Channel( 'prices' );
        // قناة عامة اسمها prices
    }

    /**
    * اسم الحدث اللي هيتبث على القناة
    */

    public function broadcastAs() {
        return 'PriceUpdated';
    }

    /**
    * البيانات اللي هيستقبلها الطرف التاني ( الواجهة )
    */

    public function broadcastWith() {
        return [
            'product_id' => $this->productId,
            'new_price' => $this->newPrice,
        ];
    }
}
