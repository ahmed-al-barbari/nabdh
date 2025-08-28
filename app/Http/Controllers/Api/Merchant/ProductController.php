<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Enums\ApiMessage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::whereHas('category.store', function ($q) {
            $q->where('user_id', Auth::id());
        })->get();

        return response()->json([
            'message'  => ApiMessage::PRODUCTS_FETCHED->value,
            'products' => $products
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|string',
            'quantity'    => 'required|integer|min:0',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'message' => ApiMessage::PRODUCT_CREATED->value,
            'product' => $product
        ], 201);
    }

    public function show($id)
    {
        $product = Product::with(['category.store'])
            ->findOrFail($id);

        return response()->json([
            'message' => ApiMessage::PRODUCT_FETCHED->value,
            'product' => [
                'id'          => $product->id,
                'name'        => $product->name,
                'description' => $product->description,
                'price'       => $product->price,
                'offer_price' => $product->offer_price,
                'offer_expiry' => $product->offer_expiry,
                'image'       => $product->image,
                'quantity'    => $product->quantity,
                'category'    => [
                    'id'   => $product->category->id,
                    'name' => $product->category->name,
                ],
                'store'       => [
                    'id'      => $product->category->store->id,
                    'name'    => $product->category->store->name,
                    'address' => $product->category->store->address,
                    'lat'     => $product->category->store->latitude,
                    'lng'     => $product->category->store->longitude,
                ]
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $this->authorizeProduct($product);

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'image'       => 'nullable|string',
            'quantity'    => 'sometimes|integer|min:0',
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

        $this->authorizeProduct($product);

        $product->delete();

        return response()->json([
            'message' => ApiMessage::PRODUCT_DELETED->value
        ]);
    }

    // التحقق إن المنتج تبع التاجر الحالي
    private function authorizeProduct(Product $product)
    {
        if ($product->category->store->user_id !== Auth::id()) {
            abort(403, ApiMessage::UNAUTHORIZED->value);
        }
    }
}
