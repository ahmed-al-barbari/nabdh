<?php
namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Offer;
use App\Models\Product;
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

        $product = Product::findOrFail($validated['product_id']);

        if ($product->category->store->user_id !== Auth::id()) {
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

        $this->authorizeOffer($offer);

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

        $this->authorizeOffer($offer);

        $offer->delete();

        return response()->json([
            'message' => ApiMessage::OFFER_DELETED->value
        ]);
    }

    // حماية التاجر
    private function authorizeOffer(Offer $offer)
    {
        if ($offer->product->category->store->user_id !== Auth::id()) {
            abort(403, ApiMessage::UNAUTHORIZED->value);
        }
    }
}
