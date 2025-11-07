<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductPriceUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Product $product)
    {
        //
    }

    /**
     * Channels to send the notification
     * Only sends if toggle is explicitly enabled (true)
     */
    public function via($notifiable): array
    {
        $channels = ['database', 'broadcast'];
        $notificationMethods = $notifiable->notification_methods ?? [];

        // Email - only if toggle is ON
        if (!empty($notificationMethods['email']) && $notificationMethods['email'] === true) {
            $channels[] = 'mail';
        }

        // SMS notifications - only if toggle is ON
        if (!empty($notificationMethods['sms']) && $notificationMethods['sms'] === true) {
            $channels[] = 'sms';
        }

        // WhatsApp notifications - only if toggle is ON
        if (!empty($notificationMethods['whatsapp']) && $notificationMethods['whatsapp'] === true) {
            $channels[] = 'whatsapp';
        }

        return $channels;
    }

    /**
     * Mail representation
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("تحديث سعر المنتج: {$this->product->name}")
            ->line("السعر الحالي للمنتج '{$this->product->name}' أصبح {$this->product->price}₪ في {$this->product->store->name}.")
            ->action('عرض المنتج', url("/products/{$this->product->id}"))
            ->line('شكراً لاستخدامك تطبيقنا!');
    }

    /**
     * SMS representation
     */
    public function toSms($notifiable): string
    {
        return "تحديث سعر: {$this->product->name} - السعر الجديد: {$this->product->price}₪ في {$this->product->store->name}";
    }

    /**
     * WhatsApp representation
     */
    public function toWhatsApp($notifiable): string
    {
        return "🔔 تحديث سعر المنتج\n\n"
             . "المنتج: {$this->product->name}\n"
             . "السعر الجديد: {$this->product->price}₪\n"
             . "المتجر: {$this->product->store->name}\n\n"
             . "شكراً لاستخدامك تطبيقنا!";
    }

    /**
     * Database / Broadcast representation
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'product_price_updated',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'store_name' => $this->product->store->name,
            'new_price' => $this->product->price,
        ];
    }
}

