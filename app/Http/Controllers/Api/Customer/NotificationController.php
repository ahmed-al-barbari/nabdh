<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserNotification;
use App\Models\Product;
use App\Enums\ApiMessage;

class NotificationController extends Controller {

    public function index() {
        $notifications = UserNotification::with( [ 'product:id,name' ] )->where( 'user_id', Auth::id() )->get();

        return response()->json( [
            'message' => ApiMessage::NOTIFICATION_LIST->value,
            'notifications' => $notifications
        ] );
    }

    public function store( Request $request ) {
        $validated = $request->validate( [
            // 'title' => 'required|string|max:255',
            'product_id' => 'required|int|exists:main_products,id',
            'type' => 'required|string|in:lt,gt',
            'target_price' => 'required|int',
        ] );

        $user = Auth::user();

        $notification = UserNotification::create( [ ...$validated, 'status' => 'active', 'user_id' => $user->id ] );

        // Check if any existing products already meet the alert condition
        $matchingProducts = $this->checkExistingPrices(
            $validated['product_id'],
            $validated['type'],
            $validated['target_price']
        );

        // Prepare response data
        $responseData = [
            'message' => ApiMessage::NOTIFICATION_CREATED->value,
            'notification' => $notification,
            'existing_matches' => []
        ];

        // If we found matching products, notify the user immediately
        if ($matchingProducts->isNotEmpty()) {
            $responseData['existing_matches'] = $matchingProducts->map(function($product) use ($validated) {
                return [
                    'product_name' => $product->name,
                    'store_name' => $product->store->name,
                    'current_price' => $product->price,
                    'target_price' => $validated['target_price'],
                    'city' => $product->store->city->name ?? null,
                ];
            });

            // Send real-time notification to user about existing matches
            $matchCount = $matchingProducts->count();
            $firstProduct = $matchingProducts->first();
            $productName = $firstProduct->mainProduct->name ?? 'المنتج';
            $lowestPrice = $validated['type'] === 'lt' 
                ? $matchingProducts->min('price') 
                : $matchingProducts->max('price');
            
            $notificationTitle = $validated['type'] === 'lt'
                ? "وجدنا {$matchCount} عرض ل{$productName} بسعر {$lowestPrice}₪ أو أقل"
                : "وجدنا {$matchCount} عرض ل{$productName} بسعر {$lowestPrice}₪ أو أكثر";
            
            $status = $validated['type'];
            
            // Send notification to user
            $user->notify(new \App\Notifications\UserNotification($notificationTitle, $status));
        }

        return response()->json($responseData, 201);
    }

    /**
     * Check existing product prices against alert condition
     * Highly efficient - only queries products with specific product_id
     */
    private function checkExistingPrices($productId, $type, $targetPrice) {
        $query = Product::with(['store.city', 'mainProduct'])
            ->where('product_id', $productId)
            ->whereHas('store', function($q) {
                $q->where('status', 'accepted'); // Only verified stores
            });

        // Apply price condition based on alert type
        if ($type === 'lt') {
            // Looking for prices LOWER than target
            $query->where('price', '<', $targetPrice);
        } else {
            // Looking for prices GREATER than target
            $query->where('price', '>', $targetPrice);
        }

        return $query->orderBy('price', $type === 'lt' ? 'asc' : 'desc')
            ->limit(10) // Only return top 10 matches for efficiency
            ->get();
    }

    public function update( Request $request, $id ) {
        $notification = UserNotification::where( 'user_id', Auth::id() )->findOrFail( $id );

        $validated = $request->validate( [
            'status' => 'nullable|string|in:active,inactive'
        ] );

        $notification->update( $validated );

        return response()->json( [
            'message' => ApiMessage::NOTIFICATION_UPDATED->value,
            'notification' => $notification
        ] );
    }

    public function destroy( $id ) {
        $notification = UserNotification::where( 'user_id', Auth::id() )->findOrFail( $id );
        $notification->delete();

        return response()->json( [
            'message' => ApiMessage::NOTIFICATION_DELETED->value
        ] );
    }

    public function changeMethodStauts( Request $request ) {
        $request->validate( [
            'status' => 'required|boolean',
            'notification_method' => 'required|in:email,sms,whats',
        ] );

        $user = Auth::user();

        $methods = $user->notification_methods ?? [];

        $methods[ $request->notification_method ] = $request->status;

        $user->notification_methods = $methods;
        $user->save();

        return response()->json( [ 'message' => 'Notification method updated successfully' ] );

    }

    public function activeNotifications() {
        $notifications = Auth::user()->notifications()->where( 'data->type', 'user_notification' )->paginate();
        return [
            'notifications' => $notifications,
            'unread_count' => $notifications->whereNull( 'read_at' )->count(),
        ];
    }

    public function markAsRead() {
        Auth::user()->notifications?->markAsRead();
        return response()->json( [], 204 );
    }
}
