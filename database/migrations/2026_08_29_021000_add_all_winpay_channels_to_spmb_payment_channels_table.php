<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SpmbPaymentChannel;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $channels = [
            [
                'code' => 'BRI',
                'name' => 'BRI Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1, // Winpay
            ],
            [
                'code' => 'PERMATA',
                'name' => 'Permata Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'BSI',
                'name' => 'BSI Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'MUAMALAT',
                'name' => 'Muamalat Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'CIMB',
                'name' => 'CIMB Niaga Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'SINARMAS',
                'name' => 'Sinarmas Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'BNC',
                'name' => 'BNC Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
        ];

        foreach ($channels as $chan) {
            SpmbPaymentChannel::updateOrCreate(
                ['code' => $chan['code'], 'payment_gateway_id' => $chan['payment_gateway_id']],
                $chan
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SpmbPaymentChannel::whereIn('code', ['BRI', 'PERMATA', 'BSI', 'MUAMALAT', 'CIMB', 'SINARMAS', 'BNC'])
            ->where('payment_gateway_id', 1)
            ->delete();
    }
};
