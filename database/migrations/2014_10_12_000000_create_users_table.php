<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->enum('role', ['admin', 'merchant', 'customer'])->default('customer');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->string('language')->default('ar');
            $table->string('currency')->default('ILS'); // شيكل
            $table->string('theme')->default('light');
            $table->enum('notification_method', ['sms', 'email','whatsapp', 'push'])
                ->default('push'); // الافتراضي مثلاً Push
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['language', 'currency', 'theme']);
        });
    }
};
