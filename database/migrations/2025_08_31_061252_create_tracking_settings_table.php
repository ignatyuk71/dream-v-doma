<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tracking_settings', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Pixel (Browser)
            $table->tinyInteger('pixel_enabled')->default(0);              // 1 = вмикати скрипт пікселя
            $table->string('pixel_id', 64)->nullable();                    // ID пікселя
            $table->char('default_currency', 3)->default('UAH');           // UAH/PLN/…

            // Виключення адмінки та згода
            $table->tinyInteger('exclude_admin')->default(1);              // 1 = не трекати адмін/даші
            $table->tinyInteger('require_consent')->default(0);            // 1 = показувати/вимагати згоду перед трекінгом

            // CAPI (Server)
            $table->tinyInteger('capi_enabled')->default(0);               // 1 = слати через Conversions API
            $table->text('capi_token')->nullable();                        // Access Token
            $table->string('capi_api_version', 10)->default('v20.0');      // версія Graph API
            $table->string('capi_test_code', 64)->nullable();              // Test Events code

            // Прапорці подій
            $table->tinyInteger('send_page_view')->default(1);
            $table->tinyInteger('send_view_content')->default(1);
            $table->tinyInteger('send_add_to_cart')->default(1);
            $table->tinyInteger('send_initiate_checkout')->default(1);
            $table->tinyInteger('send_purchase')->default(1);
            $table->tinyInteger('send_lead')->default(0);

            $table->timestamps();

            // індекси (дрібні, але корисні)
            $table->index(['pixel_enabled', 'capi_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_settings');
    }
};
