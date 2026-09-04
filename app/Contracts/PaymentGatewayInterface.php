<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Create payment transaction (VA, QRIS, or E-Wallet).
     *
     * @param float $amount
     * @param string $invoiceNo
     * @param string $method
     * @param string|null $customerName
     * @param string|null $customerPhone
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function createPayment($amount, $invoiceNo, $method, $customerName = null, $customerPhone = null);

    /**
     * Validate incoming Webhook/Callback.
     *
     * @param array $headers
     * @param array $body
     * @return bool
     */
    public function verifyCallback($headers, $body);

    /**
     * Cancel / Delete active payment transaction at Gateway (e.g. Delete VA).
     *
     * @param string $invoiceNo
     * @param array $paymentInfo
     * @return array ['success' => bool, 'message' => string, 'data' => array|null]
     */
    public function cancelPayment($invoiceNo, $paymentInfo = []);
}
