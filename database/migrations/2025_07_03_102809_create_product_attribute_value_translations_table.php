<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attribute_value_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_attribute_value_id');
            $table->string('locale')->index();
            $table->string('value')->nullable();
            $table->string('slug')->nullable();
            $table->unique(['product_attribute_value_id', 'locale'], 'uniq_pav_id_locale');
            $table->timestamps();

            $table->foreign('product_attribute_value_id', 'fk_pavt_pav_id')
                  ->references('id')->on('product_attribute_values')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_value_translations');
    }
};
