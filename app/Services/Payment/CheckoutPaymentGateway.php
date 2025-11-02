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
     * @param float $amount
     * @return PaymentResult
     */
    public function processPayment(float $amount): PaymentResult
    {
        try {
            if ($amount <= 0) {
                $msg = 'Attempted to process non-positive payment amount.';
                Log::warning('[CheckoutPaymentGateway] ' . $msg, ['amount' => $amount]);
                return PaymentResult::failure($msg);
            }

            // Replace this block with actual API client calls to an external gateway.
            Log::info('[CheckoutPaymentGateway] Simulated processing payment', ['amount' => $amount]);

            // Simulate success and a fake transaction id
            $transactionId = 'sim-' . uniqid('', true);

            return PaymentResult::success($transactionId, 'Simulated payment success.');
        } catch (Throwable $e) {
            Log::error('[CheckoutPaymentGateway] Exception during payment processing', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'amount' => $amount,
            ]);

            return PaymentResult::failure('Payment processing error: ' . $e->getMessage());
        }
    }
}
