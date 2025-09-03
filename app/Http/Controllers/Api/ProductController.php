<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MainProduct;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Enums\ApiMessage;
use App\Models\Store;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category.store'])
            ->whereHas('category.store', function ($q) {
                $q->where('user_id', Auth::id());
            })->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'offer_price' => $product->offer_price,
                    'offer_expiry' => $product->offer_expiry,
                    'image' => $product->image,
                    'quantity' => $product->quantity,
                    'category' => [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ],
                    'store' => [
                        'id' => $product->category->store->id,
                        'name' => $product->category->store->name,
                        'address' => $product->category->store->address,
                        'lat' => $product->category->store->latitude,
                        'lng' => $product->category->store->longitude,
                    ]
                ];
            });

        return response()->json([
            'message' => ApiMessage::PRODUCTS_FETCHED->value,
            'products' => $products
        ]);
    }


    public function store(Request $request)
    {
        $store = Store::where('user_id', Auth::id())->firstOrFail();
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'image' => 'nullable|string',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'message' => ApiMessage::PRODUCT_CREATED->value,
            'product' => $product
        ]);
    }


    public function show($id)
    {
        $product = Product::with(['category.store'])->findOrFail($id);

        return response()->json([
            'message' => ApiMessage::PRODUCT_FETCHED->value,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'offer_price' => $product->offer_price,
                'offer_expiry' => $product->offer_expiry,
                'image' => $product->image,
                'quantity' => $product->quantity,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                ],
                'store' => [
                    'id' => $product->category->store->id,
                    'name' => $product->category->store->name,
                    'address' => $product->category->store->address,
                    'lat' => $product->category->store->latitude,
                    'lng' => $product->category->store->longitude,
                ]
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'image' => 'nullable|string',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => ApiMessage::PRODUCT_UPDATED->value,
            'product' => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => ApiMessage::PRODUCT_DELETED->value
        ]);
    }

    public function getProducts()
    {
        $products = MainProduct::with('category:id,name')->select('id', 'name', 'category_id')->get();

        return response()->json([
            'products' => $products
        ]);
    }
}
