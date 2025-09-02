<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MainProduct;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json([
            'products' => MainProduct::with('category')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'int', 'min:5'],
            'category_id' => ['required', 'int', 'exists:categories,id'],
        ]);
        return MainProduct::create($validated);
    }
    public function update(Request $request, MainProduct $product)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'price' => ['sometimes', 'required', 'int', 'min:5'],
            'category_id' => ['sometimes', 'required', 'int', 'exists:categories,id'],
        ]);
        $product->update($validated);
        return response()->json([
            'product' => $product
        ], 200);
    }

    public function destroy(MainProduct $product)
    {
        $product->delete();
        return response()->json([

        ], 204);
    }
}
