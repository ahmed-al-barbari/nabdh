<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Models\Store;
use App\Models\Category;
use App\Enums\ApiMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function index(Store $store)
    {
        return response()->json($store->categories);
    }

    public function store(Request $request, Store $store)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category = $store->categories()->create($data);

        return response()->json([
            'message'  => ApiMessage::CATEGORY_CREATED->value,
            'category' => $category
        ]);
    }

    public function show(Store $store, Category $category)
    {
        return response()->json($category);
    }

    public function update(Request $request, Store $store, Category $category)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255'
        ]);

        $category->update($data);

        return response()->json([
            'message' => ApiMessage::CATEGORY_UPDATED->value,
            'category' => $category
        ]);
    }

    public function destroy(Store $store, Category $category)
    {
        $category->delete();

        return response()->json([
            'message' => ApiMessage::CATEGORY_DELETED->value,
        ]);
    }
}
