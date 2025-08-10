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
        Schema::create('product_colors', function (Blueprint $table) {
            $table->id();

            // Продукт, до якого належить цей набір кольорів (основний товар)
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Продукт, на який веде цей колір (може бути інший товар цієї ж групи, або цей самий)
            $table->foreignId('linked_product_id')->nullable()->constrained('products')->nullOnDelete();

            $table->string('name')->nullable();
            $table->json('url')->nullable(); // URL на сторінку цього кольору
            $table->string('icon_path')->nullable(); // Шлях до прев'ю-картинки
            $table->boolean('is_default')->default(false); // Чи основний колір

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_colors');
    }
};
