<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Note: Payment channels are maintained and seeded via PaymentGatewayAndChannelSeeder.
     */
    public function up(): void
    {
        // No-op: Data is seeded via PaymentGatewayAndChannelSeeder
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
