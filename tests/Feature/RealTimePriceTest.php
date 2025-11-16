<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Models\Product;
use App\Models\Store;
use App\Models\MainProduct;
use App\Models\Category;
use App\Events\PriceUpdated;

class RealTimePriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_update_triggers_real_time_event()
    {
        Event::fake();
        $category = Category::factory()->create();
        $mainProduct = MainProduct::factory()->create(['category_id' => $category->id]);
        $store = Store::factory()->create();
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'product_id' => $mainProduct->id,
            'price' => 10
        ]);
        $product->update(['price' => 20]);
        Event::assertDispatched(PriceUpdated::class);
    }
}
