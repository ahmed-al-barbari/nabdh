<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Enums\ApiMessage;
use Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'email' => 'required_without_all:phone|email|unique:users,email',
            'phone' => 'required_without_all:email|string|size:13|unique:users,phone',
            // 'address' => 'required|string|min:6',
            // 'role' => 'required|in:customer,merchant'
        ]);
        info(request()->phone);

        $validator->after(function ($validator) use ($request) {
            if (empty($request->email) && empty($request->phone)) {
                $validator->errors()->add('email', 'يجب إدخال البريد الإلكتروني أو رقم الهاتف.');
                $validator->errors()->add('phone', 'يجب إدخال البريد الإلكتروني أو رقم الهاتف.');
            }
        });

        $validated = $validator->validate();
        $validated['password'] = Hash::make($validated['password']);
        $validated['notification_methods'] = [
            'sms' => false,
            'email' => false,
            'whats' => false,
        ];
        $user = User::create($validated);

        Auth::guard('web')->login($user);

        // $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => ApiMessage::USER_CREATED->value,
            'user' => $user->load('store'),
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without_all:phone|email|exists:users,email',
            'phone' => 'required_without_all:email|string|size:13|exists:users,phone',
            'password' => 'required|string'
        ]);
        $validator->after(function ($validator) use ($request) {
            if (empty($request->email) && empty($request->phone)) {
                $validator->errors()->add('email', 'يجب إدخال البريد الإلكتروني أو رقم الهاتف.');
                $validator->errors()->add('phone', 'يجب إدخال البريد الإلكتروني أو رقم الهاتف.');
            }
        });

        $validated = $validator->validate();
        $user = User::where('email', $request->email)->
            orWhere('phone', $request->phone)->first();

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

        // $token = $user->createToken('auth_token')->plainTextToken;
        Auth::guard('web')->login($user);
        // auth()-login($user);
        return response()->json([
            'message' => ApiMessage::LOGIN_SUCCESS->value,
            'user' => $user->load(['store', 'city']),
            // 'access_token' => $token,
            // 'token_type' => 'Bearer'
        ]);
    }

    public function logout(Request $request)
    {
        auth()->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([], 204);
    }

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string'
        ]);
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Auth::user()->delete();
        return response()->json([], 204);

    }
}
