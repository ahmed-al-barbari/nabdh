<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\OtpService;
use App\Services\SmsService;
use App\Mail\OtpMail;

class ForgotPasswordController extends Controller
{
    protected $otpService;
    protected $smsService;

    public function __construct(OtpService $otpService, SmsService $smsService)
    {
        $this->otpService = $otpService;
        $this->smsService = $smsService;
    }

    /**
     * Send OTP to user's email or phone (WhatsApp only)
     */
    public function sendResetLink(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required_without:phone|email',
                'phone' => 'required_without:email|string',
            ]);

            $identifier = $request->email ?? $request->phone;
            $user = null;

            if ($request->email) {
                $user = User::where('email', $request->email)->first();
            } elseif ($request->phone) {
                $user = User::where('phone', $request->phone)->first();
            }

            if ($user) {
                $otp = $this->otpService->generate();
                $this->otpService->store($identifier, $otp);

                if ($request->email) {
                    Mail::to($user->email)->send(new OtpMail($otp));
                } elseif ($request->phone) {
                    $message = "رمز التحقق الخاص بك هو: {$otp}\nهذا الرمز صالح لمدة 15 دقيقة.\nNabd";
                    $this->smsService->sendWhatsApp($user->phone, $message);
                }
            }

            // Always return same message (security: non-enumeration)
            return response()->json([
                'message' => 'إذا كان الحساب موجوداً، تم إرسال رمز التحقق.'
            ]);
        } catch (\Exception $e) {
            Log::error('Forgot password error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'حدث خطأ أثناء إرسال رمز التحقق. يرجى المحاولة لاحقاً.'
            ], 500);
        }
    }
}
