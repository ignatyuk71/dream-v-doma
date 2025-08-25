<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('name')->nullable(); // Наприклад: 38, 39, Червоний
            $table->string('type')->nullable(); // Розмір, Колір
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('price_override', 10, 2)->nullable(); // інша ціна, якщо є
            $table->decimal('old_price', 10, 2)->default(0); // <— ДОДАНО ТУТ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};