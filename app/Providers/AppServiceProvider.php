<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\Channels\WhatsAppChannel;

class AppServiceProvider extends ServiceProvider {
    /**
    * Register any application services.
    */

    public function register(): void {
        // Register any services here
    }

    /**
    * Bootstrap any application services.
    */

    public function boot(): void {
        // Register custom notification channels
        Notification::extend('sms', function ($app) {
            return new SmsChannel($app->make(\App\Services\SmsService::class));
        });

        Notification::extend('whatsapp', function ($app) {
            return new WhatsAppChannel($app->make(\App\Services\SmsService::class));
        });
    }
}
