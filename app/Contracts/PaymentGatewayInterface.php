<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Create payment transaction (VA or QRIS).
     *
     * @param float $amount
     * @param string $invoiceNo
     * @param string $method
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function createPayment($amount, $invoiceNo, $method);

    /**
     * Validate incoming Webhook/Callback.
     *
     * @param array $headers
     * @param array $body
     * @return bool
     */
    public function verifyCallback($headers, $body);
}
