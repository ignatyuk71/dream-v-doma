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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('payment_method'); // naprykład: card, cash_on_delivery
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('UAH');
            $table->string('status')->default('pending'); // pending, paid, failed
            $table->string('transaction_id')->nullable(); // з платіжної системи
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
