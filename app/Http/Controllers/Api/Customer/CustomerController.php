<?php

namespace App\Http\Controllers\Api\Customer;


use App\Enums\ApiMessage;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller {
    // تحديث التفضيلات فقط

    public function updatePreferences( Request $request ) {
        $validated = $request->validate( [
            'language'            => 'in:ar,en',
            'currency'            => 'in:ILS,USD',
            'theme'               => 'in:light,dark',
            'notification_method' => 'in:sms,email,whatsapp,push',
        ] );

        $user = Auth::user();
        $user->update( $validated );

        return response()->json( [
            'message'     => ApiMessage::PREFERENCES_UPDATED->value,
            'preferences' => Arr::only( $user->toArray(), [ 'language', 'currency', 'theme', 'notification_method' ] )
        ] );
    }

    // تحديث البيانات الشخصية

    public function updateProfile( Request $request ) {
        $user = Auth::user();

       $validated = $request->validate([
    'name' => [
        'sometimes',
        'string',
        'max:255',
        'regex:/^[\pL\s\-]+$/u' // حروف فقط، مسافات وشرطات
    ],
    'email' => [
        'sometimes',
        'email',
        'unique:users,email,' . $user->id
    ],
    'phone' => [
        'sometimes',
        'string',
        'max:20',
        'regex:/^\+?\d{7,20}$/' // أرقام فقط مع + اختياري، طول 7-20 رقم
    ],
    'role' => [
        'sometimes',
        'in:customer,merchant'
    ],
    'store_name' => [
        'required_if:role,merchant',
        'string',
        'max:255',
        'regex:/^[\pL0-9\s\-]+$/u' // حروف وأرقام ومسافات وشرطات
    ],
    'store_address' => [
        'required_if:role,merchant',
        'string',
        'max:255',
        'regex:/^[\pL0-9\s\.,\-]+$/u' // حروف وأرقام ومسافات، نقاط وشرطات وفواصل
    ],
    'store_image' => [
        'nullable',
        'string' // لو لاحقًا تريد رفع صورة يمكن تغييره لـ file|image
    ],
]);

        // تحديث بيانات المستخدم
        $user->update( Arr::only( $validated, [ 'name', 'email', 'phone', 'role' ] ) );

        // إذا المستخدم غيّر حالته لتاجر
        if ( $request->role === 'merchant' ) {
            // تأكد أنه ما عندوش متجر سابق
            if ( !$user->store ) {
                Store::create( [
                    'user_id'  => $user->id,
                    'name'     => $validated[ 'store_name' ],
                    'address'  => $validated[ 'store_address' ],
                    'image'    => $validated[ 'store_image' ] ?? null,
                    'status'   => 'pending', // أول ما ينشأ يكون معلق
                ] );
            }
        }

        return response()->json( [
            'message' => ApiMessage::PROFILE_UPDATED->value,
            'user'    => $user->load( 'store' )
        ] );
    }

    /**
     * حساب درجة موثوقية المستخدم
     */
    public function getUserReliabilityScore(Request $request)
    {
        $requestedUserId = $request->input('user_id');
        $targetUser = $requestedUserId ? \App\Models\User::find($requestedUserId) : auth()->user();
        
        if (!$targetUser) {
            return response()->json([
                'message' => 'User not found',
                'score' => 0
            ], 404);
        }

        $score = 0;

        // 1️⃣ رقم الهاتف (30%) - Verified identity is crucial baseline
        $score += $targetUser->phone ? 30 : 0;

        // 2️⃣ المقايضات المكتملة (35%) - Transaction reliability is key trust indicator
        $totalTrades = \App\Models\BarterResponse::where('user_id', $targetUser->id)->count();
        $completedTrades = \App\Models\BarterResponse::where('user_id', $targetUser->id)
            ->where('status', 'completed')
            ->count();
        // Give partial credit for having trades even if not all completed (encourages participation)
        if ($totalTrades > 0) {
            $completionRate = $completedTrades / $totalTrades;
            $tradeScore = $completionRate * 35;
        } else {
            // New users: give small base score to avoid harsh penalty
            $tradeScore = 5; // 5% base score for new users
        }
        $score += $tradeScore;

        // 3️⃣ البلاغات + تقييم المنتجات (35%) - Product quality shows merchant reliability
        $productScores = [];

        foreach ($targetUser->store?->products ?? [] as $product) {
            // استخدام قيمة افتراضية 4 إذا ما فيه rating
            $productRating = $product->rating ?? 4;
            if ($productRating <= 3) continue; // فقط المنتجات بتقييم > 3

            $totalReports = $product->reports->count();
            $reviewedReports = $product->reports->where('status', 'reviewed')->count();

            // إذا ما فيه تقارير، نعتبرها جيدة (1)
            $productScore = $totalReports > 0 ? ($reviewedReports / $totalReports) : 1;
            $productScores[] = $productScore;
        }

        // إذا ما فيه منتجات محسوبة، نعتبر 100% (or give base score)
        if (count($productScores) > 0) {
            $averageProductScore = array_sum($productScores) / count($productScores);
            $score += $averageProductScore * 35;
        } else {
            // Users without products (non-merchants) get base score
            $score += 20; // 20% base score for non-merchants
        }

        return response()->json([
            'message' => 'User reliability score fetched successfully',
            'score' => round($score, 2) // قيمة بين 0 و 100
        ]);
    }
}
