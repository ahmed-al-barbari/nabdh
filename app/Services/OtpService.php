<?php

namespace App\Services;

use App\Models\PasswordOtp;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OtpService
{
    /**
     * Generate a 6-character alphanumeric OTP
     */
    public function generate(): string
    {
        return strtoupper(Str::random(6));
    }

    /**
     * Store OTP for identifier (email or phone)
     */
    public function store(string $identifier, string $otp, int $expiresInMinutes = 15): PasswordOtp
    {
        // Delete any existing OTPs for this identifier
        PasswordOtp::where('identifier', $identifier)->delete();

        return PasswordOtp::create([
            'identifier' => $identifier,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes($expiresInMinutes),
        ]);
    }

    /**
     * Verify OTP for identifier
     */
    public function verify(string $identifier, string $otp): bool
    {
        $otpRecord = PasswordOtp::where('identifier', $identifier)
            ->where('otp', strtoupper($otp))
            ->first();

        if (!$otpRecord) {
            return false;
        }

        if (Carbon::now()->isAfter($otpRecord->expires_at)) {
            $otpRecord->delete();
            return false;
        }

        return true;
    }

    /**
     * Delete OTP after successful verification
     */
    public function delete(string $identifier): void
    {
        PasswordOtp::where('identifier', $identifier)->delete();
    }
}

