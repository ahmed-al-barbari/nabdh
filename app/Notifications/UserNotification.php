<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification
{
    use Queueable;

    public function __construct(public string $title, public string $status)
    {
        //
    }

    public function via(object $notifiable): array
    {
        // إصلاح الإملاء فقط
        $channels = ['database', 'broadcast'];
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        // نستخدم $this->title بدل النص العام
        return (new MailMessage)
            ->subject('تنبيه سعر منتج')
            ->line($this->title)
            ->action('عرض المنتج', url('/'))
            ->line('شكراً لاستخدامك تطبيقنا!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'user_notification',
            'title' => $this->title,
            'status' => $this->status
        ];
    }
}
