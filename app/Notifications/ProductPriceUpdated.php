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
     */
    public function via($notifiable): array
    {
        $channels = ['database', 'broadcast'];

        // مثال على الإيميل إذا المستخدم مفعله
        if ($notifiable->notification_methods['email'] ?? false) {
            $channels[] = 'mail';
        }

        // ممكن توسع لاحقًا لـ sms أو whats
        // if ($notifiable->notification_methods['sms'] ?? false) { $channels[] = 'sms'; }
        // if ($notifiable->notification_methods['whats'] ?? false) { $channels[] = 'whats'; }

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
