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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique(); // артикул
            $table->decimal('price', 10, 2);
            $table->integer('quantity_in_stock')->default(0);
            $table->boolean('status')->default(true); // активний чи ні
            $table->boolean('is_popular')->default(false); // популярний товар
            $table->text('meta_description')->nullable();
            
            // 👇 Додаємо зв’язок із size_guides
            $table->foreignId('size_guide_id')
                ->nullable()
                ->default(null)
                ->constrained('size_guides')
                ->nullOnDelete(); // якщо сітку видалять — лишиться null

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
