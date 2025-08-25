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
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();                  // Наприклад: color, size, material
            $table->string('type')->default('text');           // Тип: text, select, checkbox
            $table->boolean('is_filterable')->default(true);   // Чи показувати у фільтрах
            $table->integer('position')->default(0);           // Порядок виводу
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
    }
};
