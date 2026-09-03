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
                'id' => 1,
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
            [
                'id' => 2,
                'name' => 'BNI SNAP QRIS MPM',
                'code' => 'bni',
                'is_active' => true,
                'settings_schema' => [
                    ['key' => 'merchant_id', 'type' => 'text', 'label' => 'Merchant ID'],
                    ['key' => 'terminal_id', 'type' => 'text', 'label' => 'Terminal ID (TID)'],
                    ['key' => 'client_id', 'type' => 'text', 'label' => 'Client ID'],
                    ['key' => 'client_secret', 'type' => 'password', 'label' => 'Client Secret'],
                    ['key' => 'private_key', 'type' => 'textarea', 'label' => 'Private Key (RSA)'],
                ],
            ],
        ];

        foreach ($gateways as $gw) {
            PaymentGateway::updateOrCreate(['id' => $gw['id']], [
                'name' => $gw['name'],
                'code' => $gw['code'],
                'is_active' => $gw['is_active'],
                'settings_schema' => $gw['settings_schema'],
            ]);
        }

        // 2. Payment Channels
        $channels = [
            // Winpay Channels (gateway id 1)
            [
                'code' => 'MANDIRI',
                'name' => 'Mandiri Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'BCA',
                'name' => 'BCA Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'BNI',
                'name' => 'BNI Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'BRI',
                'name' => 'BRI Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
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
            [
                'code' => 'QRIS',
                'name' => 'QRIS',
                'type' => 'qris',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'DANA',
                'name' => 'DANA',
                'type' => 'ewallet',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'SHOPEEPAY',
                'name' => 'ShopeePay',
                'type' => 'ewallet',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'INDOMARET',
                'name' => 'Indomaret',
                'type' => 'retail',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'ALFAMART',
                'name' => 'Alfamart',
                'type' => 'retail',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            // BNI SNAP Channels (gateway id 2)
            [
                'code' => 'BNI_QRIS',
                'name' => 'BNI SNAP QRIS',
                'type' => 'qris',
                'is_active' => true,
                'payment_gateway_id' => 2,
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
