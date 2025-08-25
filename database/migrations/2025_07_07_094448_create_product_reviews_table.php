<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('author_name')->nullable()->default(null);
            $table->unsignedTinyInteger('rating')->nullable()->default(null); // 1–5
            $table->text('content')->nullable()->default(null);
            $table->string('photo_path')->nullable()->default(null);
            $table->boolean('is_approved')->default(false); // модерація
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};