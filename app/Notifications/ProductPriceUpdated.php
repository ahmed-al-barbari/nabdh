<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ProductPriceUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Product $product,
        public float $oldPrice,
        public float $newPrice
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if (($notifiable->notification_methods['email'] ?? false)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("تحديث سعر المنتج: {$this->product->name}")
            ->line("السعر السابق: {$this->oldPrice}₪")
            ->line("السعر الحالي: {$this->newPrice}₪ في {$this->product->store->name}.")
            ->action('عرض المنتج', url("/products/{$this->product->id}"))
            ->line('شكراً لاستخدامك تطبيقنا!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'product_price_updated',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'store_name' => $this->product->store->name,
            'old_price' => $this->oldPrice,
            'new_price' => $this->newPrice,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'product_id' => $this->product->id,
            'old_price' => $this->oldPrice,
            'new_price' => $this->newPrice,
            'product_name' => $this->product->name,
        ]);
    }
}
