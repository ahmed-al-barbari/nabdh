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

        // 1️⃣ Phone Verification (20%) - Verified identity baseline
        $score += $targetUser->phone ? 20 : 0;

        // 2️⃣ Accurate Reports (30%) - Valid reports reviewed by admin
        // Count reports that were reviewed (validated by admin)
        $totalReportsByUser = \DB::table('user_report')
            ->join('reports', 'user_report.report_id', '=', 'reports.id')
            ->where('user_report.user_id', $targetUser->id)
            ->where('reports.status', 'reviewed')
            ->count();
        
        // Score based on number of accurate reports (capped at 30%)
        // 1 report = 10%, 2 reports = 20%, 3+ reports = 30%
        $reportsScore = min(30, $totalReportsByUser * 10);
        $score += $reportsScore;

        // 3️⃣ Completed Trades (30%) - Transaction reliability
        $totalTrades = \App\Models\BarterResponse::where('user_id', $targetUser->id)->count();
        $completedTrades = \App\Models\BarterResponse::where('user_id', $targetUser->id)
            ->where('status', 'completed')
            ->count();
        
        if ($totalTrades > 0) {
            $completionRate = $completedTrades / $totalTrades;
            $tradeScore = $completionRate * 30;
        } else {
            // New users with no trades get 0% (encourages participation)
            $tradeScore = 0;
        }
        $score += $tradeScore;

        // 4️⃣ Account Activity & Age (20%) - Account maturity and engagement
        $accountAge = now()->diffInMonths($targetUser->created_at);
        $hasEmail = $targetUser->email ? 1 : 0;
        $hasLocation = ($targetUser->city_id && $targetUser->share_location) ? 1 : 0;
        
        // Account age: max 12 months = 10%, older accounts get full 10%
        $ageScore = min(10, ($accountAge / 12) * 10);
        
        // Profile completeness: email (5%) + location (5%) = 10%
        $profileScore = ($hasEmail * 5) + ($hasLocation * 5);
        
        $activityScore = $ageScore + $profileScore;
        $score += $activityScore;

        // Ensure score is between 0 and 100
        $finalScore = min(100, max(0, $score));

        return response()->json([
            'message' => 'User reliability score fetched successfully',
            'score' => round($finalScore, 2) // قيمة بين 0 و 100
        ]);
    }
}
