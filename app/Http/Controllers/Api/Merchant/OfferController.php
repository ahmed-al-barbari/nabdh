<?php
namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Offer;
use Illuminate\Support\Facades\Auth;
use App\Enums\ApiMessage;

class OfferController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'       => 'required|exists:products,id',
            'discount_price'   => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|integer|min:1|max:100',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
        ]);

        // 🔐 تحقق أن المنتج يخص التاجر الحالي
        $isOwner = Auth::user()
            ->store
            ?->products()
            ->where('id', $validated['product_id'])
            ->exists();

        if (! $isOwner) {
            return response()->json([
                'message' => ApiMessage::UNAUTHORIZED->value
            ], 403);
        }

        $offer = Offer::create($validated);

        return response()->json([
            'message' => ApiMessage::OFFER_CREATED->value,
            'offer'   => $offer
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $offer = Offer::findOrFail($id);

        // 🔐 تحقق أن المنتج تبع التاجر
        $isOwner = Auth::user()
            ->store
            ?->products()
            ->where('id', $offer->product_id)
            ->exists();

        if (! $isOwner) {
            return response()->json([
                'message' => ApiMessage::UNAUTHORIZED->value
            ], 403);
        }

        $validated = $request->validate([
            'discount_price'   => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|integer|min:1|max:100',
            'start_date'       => 'sometimes|date',
            'end_date'         => 'sometimes|date|after:start_date',
            'active'           => 'boolean'
        ]);

        $offer->update($validated);

        return response()->json([
            'message' => ApiMessage::OFFER_UPDATED->value,
            'offer'   => $offer
        ]);
    }

    public function destroy($id)
    {
        $offer = Offer::findOrFail($id);

        $isOwner = Auth::user()
            ->store
            ?->products()
            ->where('id', $offer->product_id)
            ->exists();

        if (! $isOwner) {
            return response()->json([
                'message' => ApiMessage::UNAUTHORIZED->value
            ], 403);
        }

        $offer->delete();

        return response()->json([
            'message' => ApiMessage::OFFER_DELETED->value
        ]);
    }
}
