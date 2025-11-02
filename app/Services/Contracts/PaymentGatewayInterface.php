<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Services\Payment\PaymentResult;

/**
 * Interface PaymentGatewayInterface
 *
 * Abstracts payment processing.
 */
interface PaymentGatewayInterface
{
    /**
     * Process a payment of the specified amount.
     *
     * Implementations return a PaymentResult object that contains:
     *  - success (bool)
     *  - message (string|null)
     *  - transactionId (string|null)
     *
     * Implementations should throw exceptions only for truly fatal/unexpected errors.
     *
     * @param float $amount
     * @return PaymentResult
     */
    public function processPayment(float $amount): PaymentResult;
}
