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
            'stores' => $stores
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // إذا كان المستخدم تاجر، تحقق إنه ما عندوش متجر مسبق
        if ($user->role === 'merchant') {
            $existingStore = Store::where('user_id', $user->id)->first();
            if ($existingStore) {
                return response()->json([
                    'message' => ApiMessage::STORE_ALREADY_EXISTS->value,
                    'store' => $existingStore
                ], 403);
            }
        }

        // Validate المدخلات
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'image' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // إنشاء المتجر
        $store = Store::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'address' => $validated['address'],
            'image' => $validated['image'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            // حالة المتجر: إذا أدمن يكون active مباشرة، وإذا تاجر يكون pending
            'status' => $user->role === 'admin' ? 'active' : 'pending',
        ]);

        return response()->json([
            'message' => ApiMessage::STORE_CREATED->value,
            'store' => $store
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $store = Store::findOrFail($id);
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'image' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // إذا المستخدم تاجر، لا يسمح له بتغيير الحالة
        if ($user->role === 'merchant' && isset($validated['status'])) {
            unset($validated['status']);
        }


        $store->update($validated);

        return response()->json([
            'message' => ApiMessage::STORE_UPDATED->value,
            'store' => $store
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

    public function updateGeneralSettingOrCreate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'image' => 'sometimes|required|image',
            'city_id' => 'sometimes|exists:cities,id',
        ]);

        $user = Auth::user();
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file(key: 'image')->store('stores', 'public');
        }
        $user->store()->updateOrCreate([], $validated);
        return response()->json([
            'message' => ApiMessage::STORE_UPDATED->value,
            'store' => $user->store
        ]);
    }
}
