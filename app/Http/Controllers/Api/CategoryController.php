<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Enums\ApiMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller {
    public function index() {
        $categories = Category::all();
        return response()->json( [
            'message' => ApiMessage::CATEGORY_UPDATED->value,
            'categories' => $categories
        ] );
    }

    public function store( Request $request ) {
        $validated = $request->validate( [
            'name' => 'required|string|unique:categories,name',
            'description' => 'nullable|string'
        ] );

        $category = Category::create( $validated );

        return response()->json( [
            'message' => ApiMessage::CATEGORY_CREATED->value,
            'category' => $category
        ] );
    }

    public function update( Request $request, $id ) {
        $category = Category::findOrFail( $id );

        $validated = $request->validate( [
            'name' => 'required|string|unique:categories,name,' . $id,
            'description' => 'nullable|string'
        ] );

        $category->update( $validated );

        return response()->json( [
            'message' => ApiMessage::CATEGORY_UPDATED->value,
            'category' => $category
        ] );
    }

    public function destroy( $id ) {
        $category = Category::findOrFail( $id );
        $category->delete();

        return response()->json( [
            'message' => ApiMessage::CATEGORY_DELETED->value
        ] );
    }

    public function getCategories() {
        $categories = Category::select( 'id', 'name' )->get();

        return response()->json( [
            'categories' => $categories
        ] );
    }
}
