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
        return response()->json($stores);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        $store = Store::create([
            'merchant_id' => Auth::id(),
            'name'        => $validated['name'],
            'address'     => $validated['address'] ?? null,
            'description' => $validated['description'] ?? null,
            'status'      => 'active',
        ]);

        return response()->json([
            'message' => ApiMessage::STORE_CREATED->value,
            'store'   => $store
        ]);
    }

    public function show($id)
    {
        $store = Store::where('user_id', Auth::id())->findOrFail($id);
        return response()->json($store);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'    => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $store = Store::where('merchant_id', Auth::id())->findOrFail($id);

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

        return response()->json(['message' => ApiMessage::STORE_DELETED->value]);
    }
}
