<?php

namespace App\Notifications\Channels;

use App\Services\SmsService;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $message = $notification->toWhatsApp($notifiable);
        $phone = $notifiable->phone ?? $notifiable->routeNotificationFor('whatsapp');

        if ($phone && $message) {
            $this->smsService->sendWhatsApp($phone, $message);
        }
    }
}

