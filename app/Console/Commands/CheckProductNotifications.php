<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Mail;

class CheckProductNotifications extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check';
    protected $description = 'Check product prices and trigger notifications';
    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notifications = UserNotification::where('is_triggered', false)->get();

        foreach ($notifications as $notification) {
            if ($notification->product->price <= $notification->target_price) {
                $notification->update(['is_triggered' => true]);

                match ($notification->method) {
                    'sms' => $this->sendSms($notification->user->phone, "The product {$notification->product->name} reached your target price!"),
                    'whatsapp' => $this->sendWhatsapp($notification->user->phone, "The product {$notification->product->name} reached your target price!"),
                    'email' => $this->sendEmail($notification->user->email, "The product {$notification->product->name} reached your target price!"),
                };
            }
        }

        $this->info('Notifications checked successfully.');
    }

    private function sendSms($phone, $message)
    {
        // كود الإرسال SMS
    }

    private function sendWhatsapp($phone, $message)
    {
        // كود الإرسال واتساب
    }

    private function sendEmail($email, $message)
    {
        Mail::raw($message, function ($mail) use ($email) {
            $mail->to($email)
                ->subject('Price Alert Notification');
        });
    }
}
