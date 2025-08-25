<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Enums\ApiMessage;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'required|string|min:6',
            'address' => 'required|string|min:6',
            'role' => 'required|in:customer,merchant'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => $request->role,
            'status' => $request->role === 'customer' ? 'active' : 'pending'
        ]);


        $token = $user->createToken('auth_token')->plainTextToken;

       return response()->json([
            'message' => ApiMessage::USER_CREATED->value,
            'user'    => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => ApiMessage::USER_NOT_FOUND->value,
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [ApiMessage::LOGIN_FAILED->value]
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => ApiMessage::LOGIN_SUCCESS->value,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

         return response()->json([
            'message' => ApiMessage::LOGOUT_SUCCESS->value,
        ]);
    }
}
