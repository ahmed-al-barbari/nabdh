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
        Schema::table('barters', function (Blueprint $table) {
            $table->foreignId('accepted_by')->nullable()->constrained('users')->onDelete('set null')->after('user_id'); // المستخدم الذي قبل المبادل
            $table->enum('status', ['pending', 'active', 'completed'])->default('pending')->change();
            $table->string('contact_method')->nullable()->after('location'); // التواصل
            $table->string('availability')->nullable()->after('contact_method'); // التوفر
            $table->string('offer_status')->nullable()->after('availability'); // الحالة
            $table->string('quantity')->nullable()->after('offer_status'); // الكمية
            $table->string('exchange_preferences')->nullable()->after('quantity');  // طريقة الاتصال المفضلة
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barters', function (Blueprint $table) {
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->dropColumn([
                'accepted_by',
                'contact_method',
                'availability',
                'offer_status',
                'quantity',
                'exchange_preferences',
            ]);
        });
    }
};
