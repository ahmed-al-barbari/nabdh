<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;



class ResetPasswordController extends Controller
{
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!$request->email && !$request->phone) {
            return response()->json(['message' => 'Email or phone is required'], 422);
        }

        $reset = DB::table('password_resets')
            ->where(function ($q) use ($request) {
                if ($request->email) {
                    $q->where('email', $request->email);
                }
                if ($request->phone) {
                    $q->where('phone', $request->phone);
                }
            })
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return response()->json(['message' => 'Invalid or expired token'], 400);
        }

        if (Carbon::parse($reset->created_at)->addMinutes(15)->isPast()) {
            return response()->json(['message' => 'Token expired'], 400);
        }

        $user = User::where('email', $request->email)
            ->orWhere('phone', $request->phone)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password = $request->password;
        $user->save();

        DB::table('password_resets')
            ->where('email', $request->email)
            ->orWhere('phone', $request->phone)
            ->delete();

        return response()->json(['message' => 'Password reset successfully']);
    }
}
