<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class CheckoutPaymentGateway
 *
 * A lightweight, simulated payment gateway implementation.
 * Primarily intended for internal use, sandbox testing, or local development.
 *
 * This implementation mimics payment processing behavior without
 * communicating with a real third-party payment provider.
 *
 * **Responsibilities:**
 * - Validate payment amount.
 * - Simulate transaction execution and logging.
 * - Return structured results via {@see PaymentResult}.
 *
 * @package App\Services\Payment
 */
class CheckoutPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Process payment for the given amount.
     *
     * Simulates a payment request. If the amount is invalid or negative,
     * the operation fails immediately. Otherwise, a fake transaction
     * is generated and logged.
     *
     * **Error Handling:**
     * - Logs warnings for invalid amounts.
     * - Catches all exceptions and returns a failure result.
     *
     * @param float $amount
     *     The amount to charge. Must be greater than zero.
     *
     * @return PaymentResult
     *     A result object representing success or failure of the transaction.
     */
    public function processPayment(float $amount): PaymentResult
    {
        try {
            /**
             * Validate that the payment amount is positive.
             * Non-positive values are rejected as invalid.
             */
            if ($amount <= 0) {
                $msg = 'Attempted to process non-positive payment amount.';
                Log::warning('[CheckoutPaymentGateway] ' . $msg, ['amount' => $amount]);

                return PaymentResult::failure($msg);
            }

            /**
             * Simulate a successful payment request.
             * Replace this section with actual API calls in production.
             */
            Log::info('[CheckoutPaymentGateway] Simulated processing payment', ['amount' => $amount]);

            /** @var string $transactionId A unique fake transaction reference. */
            $transactionId = 'sim-' . uniqid('', true);

            // Return a simulated success result
            return PaymentResult::success($transactionId, 'Simulated payment success.');
        } catch (Throwable $e) {
            /**
             * Catch and log unexpected errors during payment processing.
             * Returns a structured failure response for downstream services.
             */
            Log::error('[CheckoutPaymentGateway] Exception during payment processing', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'amount'  => $amount,
            ]);

            return PaymentResult::failure('Payment processing error: ' . $e->getMessage());
        }
    }
}
