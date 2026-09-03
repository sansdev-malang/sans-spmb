<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentGateway;
use App\Models\SpmbPaymentChannel;

class PaymentGatewayAndChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Payment Gateways
        $gateways = [
            [
                'name' => 'Winpay Gateway',
                'code' => 'winpay',
                'is_active' => true,
                'settings_schema' => [
                    ['key' => 'merchant_id', 'type' => 'text', 'label' => 'Merchant ID'],
                    ['key' => 'client_key', 'type' => 'text', 'label' => 'Client Key'],
                    ['key' => 'client_secret', 'type' => 'password', 'label' => 'Client Secret'],
                    ['key' => 'private_key', 'type' => 'textarea', 'label' => 'Private Key (RSA)'],
                    ['key' => 'public_key', 'type' => 'textarea', 'label' => 'Public Key (RSA)'],
                ],
            ],
        ];

        foreach ($gateways as $gw) {
            PaymentGateway::updateOrCreate(['code' => $gw['code']], [
                'name' => $gw['name'],
                'is_active' => $gw['is_active'],
                'settings_schema' => $gw['settings_schema'],
            ]);
        }

        $winpayId = PaymentGateway::where('code', 'winpay')->first()?->id ?? 1;

        // 2. Payment Channels
        $channels = [
            [
                'code' => 'MANDIRI',
                'name' => 'Mandiri Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => $winpayId,
            ],
            [
                'code' => 'BCA',
                'name' => 'BCA Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => $winpayId,
            ],
            [
                'code' => 'BNI',
                'name' => 'BNI Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => $winpayId,
            ],
            [
                'code' => 'BRI',
                'name' => 'BRI Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => $winpayId,
            ],
            [
                'code' => 'BSI',
                'name' => 'BSI Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => $winpayId,
            ],
            [
                'code' => 'QRIS',
                'name' => 'QRIS',
                'type' => 'qris',
                'is_active' => true,
                'payment_gateway_id' => $winpayId,
            ],
            [
                'code' => 'DANA',
                'name' => 'DANA',
                'type' => 'ewallet',
                'is_active' => true,
                'payment_gateway_id' => $winpayId,
            ],
            [
                'code' => 'SPAY',
                'name' => 'ShopeePay',
                'type' => 'ewallet',
                'is_active' => true,
                'payment_gateway_id' => $winpayId,
            ],
            [
                'code' => 'INDOMARET',
                'name' => 'Indomaret',
                'type' => 'retail',
                'is_active' => true,
                'payment_gateway_id' => $winpayId,
            ],
            [
                'code' => 'ALFAMART',
                'name' => 'Alfamart',
                'type' => 'retail',
                'is_active' => true,
                'payment_gateway_id' => $winpayId,
            ],
        ];

        foreach ($channels as $chan) {
            SpmbPaymentChannel::updateOrCreate(
                ['code' => $chan['code'], 'payment_gateway_id' => $chan['payment_gateway_id']],
                $chan
            );
        }
    }
}
