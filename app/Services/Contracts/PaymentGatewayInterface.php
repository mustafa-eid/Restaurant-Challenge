<?php

namespace App\Services\Contracts;

interface PaymentGatewayInterface
{
    public function processPayment(float $amount): bool;
}
