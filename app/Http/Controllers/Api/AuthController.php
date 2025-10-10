<?php

namespace App\Http\Controllers\Api;

use App\Events\JoinNewUserEvent;
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
        $validated['notification_methods'] = [
            'sms' => false,
            'email' => false,
            'whats' => false,
        ];
        $user = User::create($validated);
        session()->regenerate();
        session()->regenerateToken();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Auth::login($user);

        event(new JoinNewUserEvent($user));

        // $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => ApiMessage::USER_CREATED->value,
            'user' => $user->load('store'),
        ], 201);
    }

   public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => [
            'required_without_all:phone',
            'email:rfc,dns',
            'max:255',
            'exists:users,email'
        ],
        'phone' => [
            'required_without_all:email',
            'string',
            'regex:/^\+?\d{11,15}$/', // يقبل + ورقم من 11 لـ 15 رقم
            'exists:users,phone'
        ],
        'password' => [
            'required',
            'string',
            'min:6', // أقل شيء 6 حروف
            'max:64',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/'
            // لازم يحتوي على حرف كبير وصغير ورقم ورمز
        ],
    ], [
        'email.required_without_all' => 'يجب إدخال البريد الإلكتروني أو رقم الهاتف.',
        'phone.required_without_all' => 'يجب إدخال البريد الإلكتروني أو رقم الهاتف.',
        'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
        'email.exists' => 'البريد الإلكتروني غير مسجل.',
        'phone.regex' => 'صيغة رقم الهاتف غير صحيحة. مثال: +972599123456',
        'phone.exists' => 'رقم الهاتف غير مسجل.',
        'password.required' => 'كلمة المرور مطلوبة.',
        'password.min' => 'كلمة المرور يجب ألا تقل عن 8 حروف.',
        'password.regex' => 'كلمة المرور يجب أن تحتوي على حرف كبير وصغير ورقم ورمز خاص.',
    ]);

    $validated = $validator->validate();

    $credentials = ['password' => $validated['password']];
    if (!empty($validated['email'])) {
        $credentials['email'] = strtolower(trim($validated['email']));
    } elseif (!empty($validated['phone'])) {
        $credentials['phone'] = $validated['phone'];
    }

    if (!Auth::guard('web')->attempt($credentials)) {
        throw ValidationException::withMessages([
            'email' => [ApiMessage::LOGIN_FAILED->value],
        ]);
    }

    $request->session()->regenerate();

    /** @var User $user */
    $user = Auth::user();

    return response()->json([
        'message' => ApiMessage::LOGIN_SUCCESS->value,
        'user' => $user->load(['store', 'city']),
    ]);
}

    public function logout(Request $request)
    {
        auth()->guard('web')->logout();
        session()->regenerate();
        session()->regenerateToken();
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
        session()->regenerate();
        session()->regenerateToken();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Auth::user()->delete();
        return response()->json([], 204);

    }
}
