<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->json('settings_schema');
            $table->timestamps();
        });

        // Seed initial gateways
        DB::table('payment_gateways')->insert([
            [
                'name' => 'Winpay Gateway',
                'code' => 'winpay',
                'is_active' => true,
                'settings_schema' => json_encode([
                    ['key' => 'merchant_id', 'label' => 'Merchant ID', 'type' => 'text'],
                    ['key' => 'client_key', 'label' => 'Client Key', 'type' => 'text'],
                    ['key' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password'],
                    ['key' => 'private_key', 'label' => 'Private Key (RSA)', 'type' => 'textarea'],
                    ['key' => 'public_key', 'label' => 'Public Key (RSA)', 'type' => 'textarea']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'BNI SNAP QRIS MPM',
                'code' => 'bni',
                'is_active' => true,
                'settings_schema' => json_encode([
                    ['key' => 'merchant_id', 'label' => 'Merchant ID', 'type' => 'text'],
                    ['key' => 'terminal_id', 'label' => 'Terminal ID (TID)', 'type' => 'text'],
                    ['key' => 'client_id', 'label' => 'Client ID', 'type' => 'text'],
                    ['key' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password'],
                    ['key' => 'private_key', 'label' => 'Private Key (RSA)', 'type' => 'textarea']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
