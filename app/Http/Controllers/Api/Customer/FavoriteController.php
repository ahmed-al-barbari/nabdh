<?php

namespace App\Http\Controllers\Api\Customer;

use App\Enums\ApiMessage;
use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $favorites = Favorite::with(['product', 'product.activeOffer'])
            ->where('user_id', Auth::id())
            ->paginate();

        return response()->json([
            'message' => ApiMessage::FAVORITES_FETCHED->value,
            'favorites' => $favorites
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($productId)
    {
        $favorite = Favorite::firstOrCreate([
            'user_id' => Auth::id(),
            'product_id' => $productId,
        ]);

        return response()->json([
            'message' => ApiMessage::FAVORITE_ADDED->value,
            'favorite' => $favorite
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($productId)
    {
        Favorite::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->delete();

        return response()->json([
            'message' => ApiMessage::FAVORITE_REMOVED->value,
        ]);
    }
}
