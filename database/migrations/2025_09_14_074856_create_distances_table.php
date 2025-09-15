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
        Schema::create('distances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id_one')->constrained('cities')->cascadeOnDelete();
            $table->foreignId('city_id_two')->constrained('cities')->cascadeOnDelete();
            $table->string('distance');
            $table->unique(['city_id_one', 'city_id_two']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distances');
    }
};
