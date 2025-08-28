<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Models\Store;
use App\Enums\ApiMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::where('user_id', Auth::id())->get();

        return response()->json([
            'message' => ApiMessage::STORES_FETCHED->value,
            'stores'  => $stores
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'address'   => 'required|string|max:255',
            'image'     => 'nullable|string',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $store = Store::create([
            'user_id'   => Auth::id(),
            'name'      => $validated['name'],
            'address'   => $validated['address'],
            'image'     => $validated['image'] ?? null,
            'latitude'  => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'status'    => 'pending', // أول ما ينشأ يكون معلق
        ]);

        return response()->json([
            'message' => ApiMessage::STORE_CREATED->value,
            'store'   => $store
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $store = Store::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'address'   => 'sometimes|string|max:255',
            'image'     => 'nullable|string',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status'    => 'in:active,inactive,pending',
        ]);

        $store->update($validated);

        return response()->json([
            'message' => ApiMessage::STORE_UPDATED->value,
            'store'   => $store
        ]);
    }

    public function destroy($id)
    {
        $store = Store::where('user_id', Auth::id())->findOrFail($id);
        $store->delete();

        return response()->json([
            'message' => ApiMessage::STORE_DELETED->value
        ]);
    }
}
