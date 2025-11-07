<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Product;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDailySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:daily-summary';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily price and offer summary to users via SMS/WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily summary...');

        // Get distinct latest 10 prices (unique products)
        $latestProducts = Product::with(['mainProduct:id,name', 'store:id,name'])
            ->whereHas('store', function($q) {
                $q->where('status', 'accepted');
            })
            ->select('products.*')
            ->distinct()
            ->orderBy('products.updated_at', 'desc')
            ->limit(10)
            ->get()
            ->unique('product_id')
            ->take(10);

        // If no accepted store products, get any products
        if ($latestProducts->count() == 0) {
            $latestProducts = Product::with(['mainProduct:id,name', 'store:id,name'])
                ->select('products.*')
                ->distinct()
                ->orderBy('products.updated_at', 'desc')
                ->limit(10)
                ->get()
                ->unique('product_id')
                ->take(10);
        }

        // Get distinct active offers
        $offers = Product::with(['mainProduct:id,name', 'store:id,name', 'activeOffer'])
            ->whereHas('activeOffer')
            ->whereHas('store', function($q) {
                $q->where('status', 'accepted');
            })
            ->select('products.*')
            ->distinct()
            ->orderBy('products.updated_at', 'desc')
            ->limit(10)
            ->get()
            ->unique('product_id')
            ->take(10);

        // If no accepted store offers, get any offers
        if ($offers->count() == 0) {
            $offers = Product::with(['mainProduct:id,name', 'store:id,name', 'activeOffer'])
                ->whereHas('activeOffer')
                ->select('products.*')
                ->distinct()
                ->orderBy('products.updated_at', 'desc')
                ->limit(10)
                ->get()
                ->unique('product_id')
                ->take(10);
        }

        // Format concise message (cost-effective, max 5 prices + 3 offers)
        $message = "📊 ملخص اليوم:\n\n";

        if ($latestProducts->count() > 0) {
            foreach ($latestProducts->take(5) as $product) {
                $productName = $product->mainProduct->name ?? 'منتج';
                $message .= "{$productName}: {$product->price}₪\n";
            }
        }

        if ($offers->count() > 0 && $latestProducts->count() > 0) {
            $message .= "\n";
        }

        if ($offers->count() > 0) {
            $message .= "🔥 عروض:\n";
            foreach ($offers->take(3) as $product) {
                $productName = $product->mainProduct->name ?? 'منتج';
                $message .= "{$productName}: {$product->price}₪\n";
            }
        }

        if ($latestProducts->count() == 0 && $offers->count() == 0) {
            $message = "📊 لا توجد أسعار أو عروض اليوم.\n\nNabd";
        } else {
            $message .= "\nNabd";
        }

        $this->info("Message prepared: " . strlen($message) . " characters");
        $this->info("Latest products: {$latestProducts->count()}, Offers: {$offers->count()}");

        // Get users with notifications enabled
        // IMPORTANT: Respect notification_methods toggles - only send SMS/WhatsApp if user has enabled them
        // Include users without phone numbers if they have email enabled
        $users = User::where('recive_notification', true)
            ->where(function($query) {
                $query->whereNotNull('phone')
                      ->orWhereNotNull('email');
            })
            ->get();

        $smsService = app(SmsService::class);
        $smsSent = 0;
        $whatsappSent = 0;
        $emailSent = 0;
        $skipped = 0;

        foreach ($users as $user) {
            // Check notification_methods toggles - only send if enabled
            $methods = $user->notification_methods ?? [];
            $hasSms = !empty($methods['sms']) && $methods['sms'] === true;
            $hasWhatsApp = !empty($methods['whatsapp']) && $methods['whatsapp'] === true;
            $hasEmail = !empty($methods['email']) && $methods['email'] === true;

            // Skip if no notification methods enabled
            if (!$hasSms && !$hasWhatsApp && !$hasEmail) {
                $skipped++;
                continue;
            }

            // Send SMS only if toggle is ON
            if ($hasSms && $user->phone) {
                $smsResult = $smsService->sendSms($user->phone, $message);
                if ($smsResult) {
                    $smsSent++;
                }
            }

            // Send WhatsApp only if toggle is ON
            if ($hasWhatsApp && $user->phone) {
                $whatsappResult = $smsService->sendWhatsApp($user->phone, $message);
                if ($whatsappResult) {
                    $whatsappSent++;
                }
            }

            // Send Email only if toggle is ON
            if ($hasEmail && $user->email) {
                try {
                    Mail::raw($message, function ($mail) use ($user) {
                        $mail->to($user->email)
                            ->subject('📊 ملخص اليوم - Nabd');
                    });
                    $emailSent++;
                } catch (\Exception $e) {
                    Log::error('Daily summary Email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                }
            }
        }

        $this->info("Daily summary completed:");
        $this->info("  SMS sent: {$smsSent}");
        $this->info("  WhatsApp sent: {$whatsappSent}");
        $this->info("  Email sent: {$emailSent}");
        $this->info("  Skipped (no methods enabled): {$skipped}");
        $this->info("  Total users processed: " . $users->count());
    }
}

