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
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->integer('amount')->default(350000);
            $table->string('payment_method')->nullable();
            $table->string('reference_id')->nullable(); // external ID from Winpay
            $table->json('payment_info')->nullable();   // JSON data of payment details (VA, QRIS, instructions)
            $table->string('status')->default('pending'); // pending, success, failed, expired
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
