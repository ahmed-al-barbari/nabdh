<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $title, public string $status)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channles = ['database', 'broadcast'];
        // if ($notifiable->recive_notification) {
        //     if ($notifiable->notification_methods['sms']) {
        //         array_push($channles, 'sms');
        //     }
        //     if ($notifiable->notification_methods['email']) {
        //         array_push($channles, 'mail');
        //     }
        //     if ($notifiable->notification_methods['whats']) {
        //         array_push($channles, 'whats');
        //     }
        // }
        return $channles;
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
        return [
            'type' => 'user_notification',
            'title' => $this->title,
            'status' => $this->status
        ];
    }
}
