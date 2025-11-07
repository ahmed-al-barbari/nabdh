<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserNotification;
use App\Services\SmsService;
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
     * FIXED: Now respects user's notification_methods toggle instead of deprecated 'method' field
     */
    public function handle()
    {
        $notifications = UserNotification::with('user', 'product')->where('is_triggered', false)->get();

        foreach ($notifications as $notification) {
            // Check if product exists and price condition is met
            if (!$notification->product || !$notification->user) {
                continue;
            }

            if ($notification->product->price <= $notification->target_price) {
                $notification->update(['is_triggered' => true]);

                $user = $notification->user;
                
                // Get user's notification method preferences - respect toggles
                $methods = $user->notification_methods ?? [];
                $hasSms = !empty($methods['sms']) && $methods['sms'] === true;
                $hasWhatsApp = !empty($methods['whatsapp']) && $methods['whatsapp'] === true;
                $hasEmail = !empty($methods['email']) && $methods['email'] === true;

                $message = "المنتج {$notification->product->name} وصل للسعر المطلوب: {$notification->product->price}₪";

                // Send notifications based on user's enabled methods (respecting toggles)
                if ($hasSms && $user->phone) {
                    $this->sendSms($user->phone, $message);
                }

                if ($hasWhatsApp && $user->phone) {
                    $this->sendWhatsapp($user->phone, $message);
                }

                if ($hasEmail && $user->email) {
                    $this->sendEmail($user->email, $message);
                }
            }
        }

        $this->info('Notifications checked successfully.');
    }

    private function sendSms($phone, $message)
    {
        $smsService = app(SmsService::class);
        return $smsService->sendSms($phone, $message);
    }

    private function sendWhatsapp($phone, $message)
    {
        $smsService = app(SmsService::class);
        return $smsService->sendWhatsApp($phone, $message);
    }

    private function sendEmail($email, $message)
    {
        Mail::raw($message, function ($mail) use ($email) {
            $mail->to($email)
                ->subject('Price Alert Notification');
        });
    }
}
