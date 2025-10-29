<?php

namespace App\Events;

use App\Models\Product;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewReportEvent {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
    * Create a new event instance.
    */

    public function __construct( public Product $product, public User $user ) {
        //
    }
}
