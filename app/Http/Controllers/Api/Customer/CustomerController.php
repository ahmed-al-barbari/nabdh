<?php

namespace App\Http\Controllers\Api\Customer;


use App\Enums\ApiMessage;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    // تحديث التفضيلات فقط

    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'language'            => 'in:ar,en',
            'currency'            => 'in:ILS,USD',
            'theme'               => 'in:light,dark',
            'notification_method' => 'in:sms,email,whatsapp,push',
        ]);

        $user = Auth::user();
        $user->update($validated);

        return response()->json([
            'message'     => ApiMessage::PREFERENCES_UPDATED->value,
            'preferences' => Arr::only($user->toArray(), ['language', 'currency', 'theme', 'notification_method'])
        ]);
    }

    // تحديث البيانات الشخصية

    public function updateProfile(Request $request)
    {
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
        $user->update(Arr::only($validated, ['name', 'email', 'phone', 'role']));

        // إذا المستخدم غيّر حالته لتاجر
        if ($request->role === 'merchant') {
            // تأكد أنه ما عندوش متجر سابق
            if (!$user->store) {
                Store::create([
                    'user_id'  => $user->id,
                    'name'     => $validated['store_name'],
                    'address'  => $validated['store_address'],
                    'image'    => $validated['store_image'] ?? null,
                    'status'   => 'pending', // أول ما ينشأ يكون معلق
                ]);
            }
        }

        return response()->json([
            'message' => ApiMessage::PROFILE_UPDATED->value,
            'user'    => $user->load('store')
        ]);
    }

    /**
     * حساب درجة موثوقية المستخدم
     */
    public function getUserReliabilityScore()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $score = 0;

        // 1️⃣ رقم الهاتف (20%)
        $score += $user->phone ? 20 : 0;

        // 2️⃣ المقايضات المكتملة (40%)
        $totalTrades = \App\Models\BarterResponse::where('user_id', $user->id)->count();
        $completedTrades = \App\Models\BarterResponse::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();
        $tradeScore = $totalTrades > 0 ? ($completedTrades / $totalTrades) * 40 : 0;
        $score += $tradeScore;

        // 3️⃣ البلاغات + تقييم المنتجات (40%)
        $productScores = [];

        foreach ($user->store?->products ?? [] as $product) {
            $productRating = $product->rating ?? 4;

            if ($productRating <= 3) continue;

            $totalReports = $product->reports->count();
            $completedReports = $product->reports->where('status', 'completed')->count();

            $productScore = $totalReports > 0 ? ($completedReports / $totalReports) : 1;
            $productScores[] = $productScore;
        }

        $averageProductScore = count($productScores) > 0 ? array_sum($productScores) / count($productScores) : 1;
        $score += $averageProductScore * 40;

        return response()->json([
            'message' => 'User reliability score fetched successfully',
            'score' => round($score, 2),
        ]);
    }
}
