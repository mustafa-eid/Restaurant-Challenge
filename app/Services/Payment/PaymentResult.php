<?php

declare(strict_types=1);

namespace App\Services\Payment;

/**
 * Class PaymentResult
 *
 * Simple immutable result object for payment gateways.
 */
final class PaymentResult
{
    public bool $success;
    public ?string $message;
    public ?string $transactionId;

    public function __construct(bool $success, ?string $message = null, ?string $transactionId = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->transactionId = $transactionId;
    }

    public static function success(?string $transactionId = null, ?string $message = null): self
    {
        return new self(true, $message, $transactionId);
    }

    public static function failure(?string $message = null, ?string $transactionId = null): self
    {
        return new self(false, $message, $transactionId);
    }
}
