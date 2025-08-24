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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // Посилання на замовлення
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Продукт (може бути видалений у каталозі — тоді залишаємо null)
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            // Варіант продукту (розмір/колір) — теж nullable на випадок змін у каталозі
            $table->foreignId('product_variant_id')
                ->nullable()
                ->constrained('product_variants')
                ->nullOnDelete();

            // Знімок даних на момент покупки
            $table->string('product_name');       // локалізована назва
            $table->string('variant_sku', 100)->nullable();
            $table->string('size', 50)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('image_url', 512)->nullable();
            $table->json('attributes_json')->nullable(); // запас для майбутніх атрибутів

            // Кількість і ціни (за одиницю та по рядку)
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price', 10, 2); // ціна за одиницю на момент покупки
            $table->decimal('total', 10, 2); // price * quantity

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
