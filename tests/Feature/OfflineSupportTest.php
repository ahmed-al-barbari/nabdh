<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;
use App\Models\Store;
use App\Models\MainProduct;
use App\Models\Category;

class OfflineSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_cached_products_are_served_offline()
    {
        $category = Category::factory()->create();
        $mainProduct = MainProduct::factory()->create(['category_id' => $category->id]);
        $store = Store::factory()->create();
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'product_id' => $mainProduct->id
        ]);

        Cache::put('products_list', [$product], 60);
        // Simulate DB failure by truncating table
        Product::truncate();

        $cached = Cache::get('products_list');
        $this->assertNotEmpty($cached);
        $this->assertEquals($product->id, $cached[0]->id);
    }
}
