<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class CheckoutPaymentGateway
 *
 * Lightweight payment gateway implementation for internal/sandbox usage.
 */
class CheckoutPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Process payment for the given amount.
     *
     * In a real gateway you'd call external APIs, handle responses,
     * throw specific exceptions on critical errors, and return boolean
     * for success/failure.
     *
     * @param float $amount
     * @return bool
     */
    public function processPayment(float $amount): bool
    {
        try {
            if ($amount <= 0) {
                Log::warning('Attempted to process non-positive payment amount.', ['amount' => $amount]);
                return false;
            }

            // Simulated processing — replace with real gateway calls.
            Log::info('CheckoutPaymentGateway: processing payment', ['amount' => $amount]);

            // Simulate success
            return true;
        } catch (Throwable $e) {
            Log::error('CheckoutPaymentGateway error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}
