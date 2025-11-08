<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\OtpService;

class OtpController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function verify(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string', // email or phone
            'otp' => 'required|string|min:6|max:6',
        ]);

        if (!$this->otpService->verify($request->identifier, $request->otp)) {
            return response()->json(['message' => 'الكود غير صحيح، يرجى المحاولة مرة أخرى'], 400);
        }

        $token = base64_encode(json_encode([
            'identifier' => $request->identifier,
            'verified_at' => now(),
        ]));

        // Don't delete OTP here - it will be deleted after password reset
        // This allows the OTP to be verified again in ResetPasswordController

        return response()->json([
            'message' => 'تم التحقق بنجاح',
            'token' => $token,
        ]);
    }
}
