<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Barter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\ApiMessage;
use Illuminate\Support\Facades\Storage;

class BarterController extends Controller
{
    public function publicIndex()
    {
        $barters = Barter::paginate(10);
        return response()->json([
            'message' => ApiMessage::BARTER_FETCHED->value,
            'barters' => $barters
        ]);
    }

    public function show($id)
    {
        $barter = Barter::findOrFail($id);
        return response()->json([
            'message' => ApiMessage::BARTER_FETCHED->value,
            'barter' => $barter
        ]);
    }

    public function index()
    {
        $barters = Barter::with([
            'user' => function ($query) {
                $query->withCount([
                    'barters as batar_count' => function ($q) {
                        $q->where('status', 'completed');
                    }
                ]);
            }
        ])
            ->where('status', 'active')
            ->paginate(10);

        return response()->json([
            'message' => ApiMessage::BARTER_FETCHED->value,
            'barters' => $barters
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'offer_item' => 'required|string|max:255',
            'request_item' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'image' => 'nullable|image',
            'quantity' => 'nullable|string|max:255',
            'contact_method' => 'nullable|string|max:255',
            'availability' => 'nullable|string|max:255',
            'exchange_preferences' => 'nullable|string|max:255',
            'offer_status' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('barters', 'public');
            $validated['image'] = Storage::url($path);
        }

        $barter = Barter::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'status' => 'active' // ثابت عند الإنشاء
        ]));

        return response()->json([
            'message' => ApiMessage::BARTER_CREATED->value,
            'barter' => $barter
        ]);
    }

    public function update(Request $request, $id)
    {
        $barter = Barter::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'offer_item' => 'required|string|max:255',
            'request_item' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'image' => 'nullable|image',
            'quantity' => 'nullable|string|max:255',
            'contact_method' => 'nullable|string|max:255',
            'availability' => 'nullable|string|max:255',
            'exchange_preferences' => 'nullable|string|max:255',
            'offer_status' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            // حذف الصورة القديمة لو موجودة
            if ($barter->image) {
                $oldPath = str_replace('/storage/', '', $barter->image);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('barters', 'public');
            $validated['image'] = Storage::url($path);
        }

        $barter->update($validated);

        return response()->json([
            'message' => ApiMessage::BARTER_UPDATED->value,
            'barter' => $barter
        ]);
    }
    public function destroy($id)
    {
        $barter = Barter::where('user_id', Auth::id())->findOrFail($id);
        $barter->delete();

        return response()->json([
            'message' => ApiMessage::BARTER_DELETED->value
        ]);
    }
}
