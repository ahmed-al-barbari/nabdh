<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\OtpService;

class ResetPasswordController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'token' => 'required|string|size:6', // OTP is 6 characters
            'password' => 'required|string|min:8|confirmed',
        ]);

        $identifier = $request->email ?? $request->phone;

        if (!$identifier) {
            return response()->json([
                'message' => 'يجب إدخال البريد الإلكتروني أو رقم الهاتف'
            ], 422);
        }

        // Verify OTP
        if (!$this->otpService->verify($identifier, $request->token)) {
            return response()->json([
                'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية'
            ], 422);
        }

        // Find user by email or phone
        $user = null;
        if ($request->email) {
            $user = User::where('email', $request->email)->first();
        } elseif ($request->phone) {
            $user = User::where('phone', $request->phone)->first();
        }

        if (!$user) {
            return response()->json([
                'message' => 'المستخدم غير موجود'
            ], 404);
        }

        // Reset password - pass plain password, User model mutator will hash it
        $user->password = $request->password;
        $user->save();

        // Delete OTP after successful password reset
        $this->otpService->delete($identifier);

        return response()->json([
            'message' => 'تم إعادة تعيين كلمة المرور بنجاح'
        ]);
    }
}
