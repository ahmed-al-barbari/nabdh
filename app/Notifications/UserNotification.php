<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $title, public string $status, public ?int $alert_id = null)
    {
        // initialize notification with title, status, and optional alert_id
    }

    /**
     * Get the notification's delivery channels.
     * Includes SMS/WhatsApp for alerts if user has them enabled
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];
        $methods = $notifiable->notification_methods ?? [];
        
        // Email for alerts - only if toggle is ON
        if (!empty($methods['email']) && $methods['email'] === true) {
            $channels[] = 'mail';
        }
        
        // SMS for alerts - only if toggle is ON
        if (!empty($methods['sms']) && $methods['sms'] === true) {
            $channels[] = 'sms';
        }
        
        // WhatsApp for alerts - only if toggle is ON
        if (!empty($methods['whatsapp']) && $methods['whatsapp'] === true) {
            $channels[] = 'whatsapp';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تنبيه سعر - نبض')
            ->line($this->title)
            ->line('شكراً لاستخدامك تطبيقنا!');
    }

    /**
     * SMS representation - concise alert format
     */
    public function toSms($notifiable): string
    {
        // Keep it simple - just the title (already formatted in listener)
        return $this->title;
    }

    /**
     * WhatsApp representation - same format as SMS
     */
    public function toWhatsApp($notifiable): string
    {
        // Same format for WhatsApp
        return $this->title;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $data = [
            'type' => 'user_notification',
            'title' => $this->title,
            'status' => $this->status
        ];
        
        // Include alert_id if provided (for triggering alerts)
        if ($this->alert_id !== null) {
            $data['alert_id'] = $this->alert_id;
        }
        
        return $data;
    }
}
