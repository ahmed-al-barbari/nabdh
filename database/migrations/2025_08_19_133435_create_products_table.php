<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade'); // كل منتج مرتبط بفئة
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // سعر المنتج
            $table->string('image')->nullable(); // صورة المنتج
            $table->integer('quantity')->default(0); // الكمية المتاحة
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
