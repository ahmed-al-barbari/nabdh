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
        // $products = Product::with(['category.store'])
        //     ->whereHas('category.store', function ($q) {
        //         $q->where('user_id', Auth::id());
        //     })->get()
        //     ->map(function ($product) {
        //         return [
        //             'id' => $product->id,
        //             'name' => $product->name,
        //             'description' => $product->description,
        //             'price' => $product->price,
        //             'offer_price' => $product->offer_price,
        //             'offer_expiry' => $product->offer_expiry,
        //             'image' => $product->image,
        //             'quantity' => $product->quantity,
        //             'category' => [
        //                 'id' => $product->category->id,
        //                 'name' => $product->category->name,
        //             ],
        //             'store' => [
        //                 'id' => $product->category->store->id,
        //                 'name' => $product->category->store->name,
        //                 'address' => $product->category->store->address,
        //                 'lat' => $product->category->store->latitude,
        //                 'lng' => $product->category->store->longitude,
        //             ]
        //         ];
        //     });

        $products = Auth::user()->store?->products()->with(['store:id,name,address', 'activeOffer'])->paginate() ?? collect();

        return response()->json([
            'message' => ApiMessage::PRODUCTS_FETCHED->value,
            'products' => $products->isNotEmpty() ? $products : [],
        ]);
    }


    public function store(Request $request)
    {
        $store = Store::where('user_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            // 'store_id' => 'required|exists:stores,id',
            'product_id' => 'required|exists:main_products,id',
            // 'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            // 'quantity' => 'required|integer',
            'image' => 'required|image',
        ]);

        if ($store->products()->where('product_id', $request->product_id)->exists()) {
            return response()->json([
                'message' => 'this product exits',
            ]);
        }

        // $validated['image'] = $request->file('image')->store('/products');
        $validated['store_id'] = $store->id;
        $product = Product::create($validated);

        return response()->json([
            'message' => ApiMessage::PRODUCT_CREATED->value,
            'product' => $product
        ]);
    }


    public function show($id)
    {
        // $product = Product::with(['category.store'])->findOrFail($id);

        $product = Auth::user()->store->products()->with(['store:id,name,address'])->where('id', $id)->first();
        return response()->json([
            'message' => ApiMessage::PRODUCT_FETCHED->value,
            'product' => $product,
        ]);
        // return response()->json([
        //     'message' => ApiMessage::PRODUCT_FETCHED->value,
        //     'product' => [
        //         'id' => $product->id,
        //         'name' => $product->name,
        //         'description' => $product->description,
        //         'price' => $product->price,
        //         'offer_price' => $product->offer_price,
        //         'offer_expiry' => $product->offer_expiry,
        //         'image' => $product->image,
        //         'quantity' => $product->quantity,
        //         'category' => [
        //             'id' => $product->category->id,
        //             'name' => $product->category->name,
        //         ],
        //         'store' => [
        //             'id' => $product->category->store->id,
        //             'name' => $product->category->store->name,
        //             'address' => $product->category->store->address,
        //             'lat' => $product->category->store->latitude,
        //             'lng' => $product->category->store->longitude,
        //         ]
        //     ]
        // ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            // 'store_id' => 'required|exists:stores,id',
            // 'category_id' => 'required|exists:categories,id',
            'product_id' => 'sometimes|required|exists:main_products,id',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric',
            // 'quantity' => 'required|integer',
            'image' => 'sometimes|required|file',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => ApiMessage::PRODUCT_UPDATED->value,
            'product' => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Auth::user()->store->products()->where('id', $id)->first();
        $product?->delete();

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

    public function viewProduct(Product $product)
    {
        info('hi' . $product);

        return response()->json([
            'message' => ApiMessage::PRODUCT_FETCHED->value,
            'product' => $product->append(['is_reported']),
        ]);
    }
    public function lastProduct()
    {
        return Product::with('activeOffer')->latest()->paginate();
    }

    public function productHasOffer()
    {
        return Product::with('activeOffer')->whereHas('activeOffer')->paginate();
    }


}
