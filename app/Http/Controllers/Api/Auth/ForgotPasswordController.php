<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;


class ForgotPasswordController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        if (!$request->email && !$request->phone) {
            return response()->json(['message' => 'Email or phone is required'], 422);
        }

        $user = \App\Models\User::when($request->email ?? false, function ($q, $email) {
            $q->where('email', $email);
        })
            ->orWhere(function ($q) use ($request) {
                $q->when($request->phone ?? false, function ($q, $phone) {
                    $q->where('phone', $phone);
                });
            })
            ->first();

        if ($user) {
            $otp = rand(100000, 999999);

            DB::table('password_resets')->updateOrInsert(
                [
                    'email' => $request->email,
                    'phone' => $request->phone,
                ],
                [
                    'token' => $otp,
                    'created_at' => Carbon::now(),
                ]
            );

            if ($request->email) {
                Mail::raw("Your reset code is: $otp", function ($message) use ($request) {
                    $message->to($request->email)->subject('Password Reset Code');
                });
            }

            if ($request->phone) {
                app(\App\Services\SmsService::class)->sendSms(
                    $request->phone,
                    "Your reset code is: $otp"
                );
            }
        }

        return response()->json(['message' => 'If the account exists, a reset code has been sent']);
    }

}
