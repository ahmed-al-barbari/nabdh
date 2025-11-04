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
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
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
