<?php

namespace App\Http\Controllers;

use Hash;
use Illuminate\Http\Request;


use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'phone' => $googleUser->phone,
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(16)),
                ]
            );

            Auth::login($user);

            return response()->json([
                'message' => 'Logged in successfully',
                'user' => $user->load(['store', 'city']),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to login with Google'], 500);
        }
    }
}
