<?php

declare(strict_types=1);

namespace App\Services\Contracts;

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
     * Implementations should throw exceptions on fatal errors or
     * return false for recoverable failures. Returning true indicates success.
     *
     * @param float $amount
     * @return bool
     */
    public function processPayment(float $amount): bool;
}
