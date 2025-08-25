<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDeliveriesTable extends Migration
{
    public function up()
    {
        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('delivery_type'); // branch, postomat, courier
            $table->string('np_ref')->nullable(); // Ref відділення НП
            $table->string('np_description')->nullable(); // Назва відділення
            $table->string('np_address')->nullable(); // Адреса відділення
            $table->string('courier_address')->nullable(); // Адреса кур’єра, якщо кур’єром
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_deliveries');
    }
}
