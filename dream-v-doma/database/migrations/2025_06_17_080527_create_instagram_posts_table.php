<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('instagram_posts', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('alt')->nullable(); // 👉 Додано alt
            $table->string('link')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }
    
    public function down(): void {
        Schema::dropIfExists('instagram_posts');
    }
    
};
