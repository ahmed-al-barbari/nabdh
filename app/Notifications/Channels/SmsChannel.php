<?php

namespace App\Notifications\Channels;

use App\Services\SmsService;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toSms')) {
            return;
        }

        $message = $notification->toSms($notifiable);
        $phone = $notifiable->phone ?? $notifiable->routeNotificationFor('sms');

        if ($phone && $message) {
            $this->smsService->sendSms($phone, $message);
        }
    }
}

