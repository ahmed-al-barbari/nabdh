<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\User;
use App\Models\Alert;
use App\Models\Barter;
use App\Models\Message;
use App\Enums\ApiMessage;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    // تحديث التفضيلات فقط
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'language' => 'in:ar,en',
            'currency' => 'in:ILS,USD',
            'theme'    => 'in:light,dark',
        ]);

        $user = Auth::user();
        $user->update($validated);

        return response()->json([
            'message'     => ApiMessage::PREFERENCES_UPDATED->value,
            'preferences' => Arr::only($user->toArray(), ['language', 'currency', 'theme'])
        ]);
    }

    // تحديث البيانات الشخصية
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,'.$user->id,
            'phone'    => 'sometimes|string|min:6',
            'password' => 'sometimes|string|min:6|confirmed', // يتطلب password_confirmation
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => ApiMessage::PROFILE_UPDATED->value,
            'user'    => Arr::only($user->toArray(), ['name','email','phone','language','currency','theme'])
        ]);
    }
}
