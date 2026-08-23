<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Services\WinpayService;
use App\Services\BniSnapService;

class PaymentGatewayFactory
{
    /**
     * Create gateway service instance dynamically.
     *
     * @param string $gatewayCode
     * @return PaymentGatewayInterface
     * @throws \Exception
     */
    public static function make($gatewayCode)
    {
        $code = strtolower($gatewayCode);

        if ($code === 'winpay') {
            return app(WinpayService::class);
        }

        if ($code === 'bni_va' || $code === 'qris_bni' || $code === 'bni') {
            return new BniSnapService($gatewayCode);
        }

        throw new \Exception("Payment gateway service untuk kode '{$gatewayCode}' belum terdaftar.");
    }
}
