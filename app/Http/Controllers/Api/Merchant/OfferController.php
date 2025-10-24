<?php
namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Offer;
use Illuminate\Support\Facades\Auth;
use App\Enums\ApiMessage;

class OfferController extends Controller {
    public function store( Request $request ) {
        $validated = $request->validate( [
            'product_id' => 'required|exists:products,id',
            'discount_price' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|integer|min:1|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'active' => 'sometimes|boolean',
        ] );

        //  تحقق أن المنتج يخص التاجر الحالي
        $isOwner = Auth::user()
        ->store
        ?->products()
        ->where( 'id', $validated[ 'product_id' ] )
        ->exists();

        if ( !$isOwner ) {
            return response()->json( [
                'message' => ApiMessage::UNAUTHORIZED->value
            ], 403 );
        }

        $offer = Offer::create( $validated );
        return response()->json( [
            'message' => ApiMessage::OFFER_CREATED->value,
            'offer' => $offer
        ], 201 );
    }

    public function update( Request $request, $id ) {
        $offer = Offer::findOrFail( $id );

        // تحقق أن المنتج تبع التاجر
        $isOwner = Auth::user()
        ->store
        ?->products()
        ->where( 'id', $offer->product_id )
        ->exists();

        if ( !$isOwner ) {
            return response()->json( [
                'message' => ApiMessage::UNAUTHORIZED->value
            ], 403 );
        }

        $validated = $request->validate( [
            'discount_price' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|integer|min:1|max:100',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'active' => 'boolean'
        ] );

        $offer->update( $validated );

        return response()->json( [
            'message' => ApiMessage::OFFER_UPDATED->value,
            'offer' => $offer
        ] );
    }

    public function destroy( $id ) {
        \Log::info('Attempting to delete offer', ['offer_id' => $id, 'user_id' => Auth::id()]);
        
        $offer = Offer::findOrFail( $id );

        $userStore = Auth::user()->store;

        // تحقق إن المستخدم عنده متجر أولاً
        if ( !$userStore ) {
            \Log::warning('User has no store', ['user_id' => Auth::id()]);
            return response()->json( [
                'message' => ApiMessage::UNAUTHORIZED->value
            ], 403 );
        }

        // تحقق إن العرض تابع لمتجر المستخدم فعلاً
        $isOwner = $offer->product->store_id === $userStore->id;

        if ( !$isOwner ) {
            \Log::warning('User does not own this offer', [
                'user_id' => Auth::id(),
                'offer_id' => $id,
                'product_store_id' => $offer->product->store_id,
                'user_store_id' => $userStore->id
            ]);
            return response()->json( [
                'message' => ApiMessage::UNAUTHORIZED->value
            ], 403 );
        }

        try {
            $deleted = $offer->delete();
            
            \Log::info('Offer deleted', ['offer_id' => $id, 'success' => $deleted]);
            
            // Verify the offer is actually deleted from database
            $stillExists = Offer::find($id);
            if ($stillExists) {
                \Log::error('Offer still exists after delete!', ['offer_id' => $id]);
                return response()->json([
                    'message' => 'Failed to delete offer from database',
                    'deleted' => false
                ], 500);
            }

            return response()->json( [
                'message' => ApiMessage::OFFER_DELETED->value,
                'deleted' => true
            ], 200 );
        } catch (\Exception $e) {
            \Log::error('Error deleting offer', [
                'offer_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error deleting offer: ' . $e->getMessage(),
                'deleted' => false
            ], 500);
        }
    }

}
