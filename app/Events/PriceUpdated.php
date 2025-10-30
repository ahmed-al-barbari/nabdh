<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
// مهم عشان البث
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class PriceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $productId;
    public $newPrice;
    public $productName;

    /**
     * Create a new event instance.
     */

    public function __construct($productId, $newPrice,$productName)
    {
        $this->productId = $productId;
        $this->newPrice = $newPrice;
        $this->productName=$productName;
    }

    /**
     * القناة اللي هيتم البث عليها
     */

    public function broadcastOn()
    {
        return new Channel('prices');
        // قناة عامة اسمها prices
    }

    /**
     * اسم الحدث اللي هيتبث على القناة
     */

    public function broadcastAs()
    {
        return 'PriceUpdated';
    }

    /**
     * البيانات اللي هيستقبلها الطرف التاني ( الواجهة )
     */

    public function broadcastWith()
    {
        return [
            'product_id' => $this->productId,
            'new_price' => $this->newPrice,
            'product_name' => $this->productName ?? null,
            'store_name' => $this->storeName ?? null,
        ];
    }
}
