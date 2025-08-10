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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('title')->nullable();         // Великий текст
            $table->string('subtitle')->nullable();      // Маленький текст
            $table->string('button_text')->nullable();   // Текст кнопки
            $table->string('button_link')->nullable();   // Посилання
            $table->boolean('is_active')->default(true); // Активність
            $table->unsignedInteger('sort_order')->default(0); // Порядок (опціонально)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
