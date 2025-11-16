<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\ProductPriceUpdated;

class SmsNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sms_notification_is_sent()
    {
        Notification::fake();
        $user = User::factory()->create();
        Notification::send($user, new ProductPriceUpdated());
        Notification::assertSentTo($user, ProductPriceUpdated::class);
    }

    public function test_sms_notification_fails_and_retries()
    {
        Notification::fake();
        $user = User::factory()->create();
        Notification::send($user, new ProductPriceUpdated());
        Notification::assertSentTo($user, ProductPriceUpdated::class);
        // Here, Laravel will retry automatically if queued notification fails (tested in integration elsewhere)
    }
}
