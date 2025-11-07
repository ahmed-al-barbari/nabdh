<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $twilio;
    protected $fromNumber;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->fromNumber = config('services.twilio.phone_number');

        if ($sid && $token) {
            try {
                $this->twilio = new Client($sid, $token);
            } catch (\Exception $e) {
                Log::error('Failed to initialize Twilio client', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Send SMS message
     */
    public function sendSms(string $to, string $message): bool
    {
        if (!$this->twilio || !$this->fromNumber) {
            Log::warning('Twilio not configured. SMS not sent.', ['to' => $to]);
            return false;
        }

        try {
            // Format phone number (ensure + prefix)
            $to = $this->formatPhoneNumber($to);
            
            $this->twilio->messages->create($to, [
                'from' => $this->fromNumber,
                'body' => $message
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send WhatsApp message via Twilio
     */
    public function sendWhatsApp(string $to, string $message): bool
    {
        if (!$this->twilio) {
            Log::warning('Twilio not configured. WhatsApp not sent.', ['to' => $to]);
            return false;
        }

        try {
            $to = $this->formatPhoneNumber($to);
            $from = config('services.twilio.whatsapp_from', 'whatsapp:+14155238886'); // Twilio Sandbox

            $this->twilio->messages->create("whatsapp:{$to}", [
                'from' => $from,
                'body' => $message
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('WhatsApp sending failed', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Format phone number to E.164 format
     * Keeps +972 or +970 if already present
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9+]/', '', trim($phone));
        
        // If already has +972, keep it
        if (str_starts_with($cleaned, '+972')) {
            return '+972' . ltrim(substr($cleaned, 4), '0');
        }
        
        // If already has +970, keep it
        if (str_starts_with($cleaned, '+970')) {
            return '+970' . ltrim(substr($cleaned, 4), '0');
        }
        
        // If has 972 without +, add +
        if (str_starts_with($cleaned, '972')) {
            return '+972' . ltrim(substr($cleaned, 3), '0');
        }
        
        // If has 970 without +, add +
        if (str_starts_with($cleaned, '970')) {
            return '+970' . ltrim(substr($cleaned, 3), '0');
        }
        
        // Default: add +970
        $number = ltrim(ltrim($cleaned, '+'), '0');
        return '+970' . $number;
    }
}
