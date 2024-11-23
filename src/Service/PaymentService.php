<?php

namespace App\Service;

class PaymentService
{
    public function maskCardNumber(string $cardNumber): string
    {
        $masked = str_repeat('*', strlen($cardNumber) - 4) . substr($cardNumber, -4);
        return implode(' ', str_split($masked, 4));
    }
}