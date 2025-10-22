<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;


use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;


class OtpController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string', // email or phone
            'otp' => 'required|string|min:6|max:6',
        ]);

        $reset = DB::table('password_resets')
            ->where(function ($q) use ($request) {
                $q->where('email', $request->identifier)
                    ->orWhere('phone', $request->identifier);
            })
            ->where('token', $request->otp)
            ->first();

        if (!$reset) {
            return response()->json(['message' => 'الكود غير صحيح'], 400);
        }

        if (Carbon::parse($reset->created_at)->addMinutes(15)->isPast()) {
            return response()->json(['message' => 'الكود منتهي الصلاحية'], 400);
        }

        $token = base64_encode(json_encode([
            'identifier' => $request->identifier,
            'verified_at' => now(),
        ]));

        return response()->json([
            'message' => 'تم التحقق بنجاح',
            'token' => $token,
        ]);
    }
}
