<?php

namespace App\Services\Payment;

use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

class CheckoutPaymentGateway implements PaymentGatewayInterface
{
    public function processPayment(float $amount): bool
    {
        // Simulate fast, lightweight payment
        Log::info("Payment processed successfully: {$amount}");
        return true;
    }
}
