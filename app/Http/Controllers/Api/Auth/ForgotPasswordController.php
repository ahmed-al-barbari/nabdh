<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\ResetPasswordMail;

class ForgotPasswordController extends Controller
{
    /**
     * Send reset link to user's email (frontend URL included)
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Non-enumeration: always return same message to caller
        $email = $request->email;

        $user = User::where('email', $email)->first();

        if ($user) {
            // create token using Laravel broker
            $token = Password::createToken($user);

            // build frontend reset url (configure FRONTEND_URL in env)
            $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
            $resetUrl = $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);

            // send Mailable (you can customize view)
            Mail::to($user->email)->send(new ResetPasswordMail($user, $resetUrl));
        }

        // Always respond with the same message (security: non-enumeration)
        return response()->json([
            'message' => 'If the account exists, a reset link has been sent to the email.'
        ]);
    }
}
