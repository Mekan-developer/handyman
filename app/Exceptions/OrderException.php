<?php

namespace App\Exceptions;

use RuntimeException;

class OrderException extends RuntimeException
{
    public static function masterAccessExpired(): self
    {
        return new self('Master access expired or master is inactive.');
    }

    public static function cityMismatch(): self
    {
        return new self('Master city does not match the order city.');
    }

    public static function categoryMismatch(): self
    {
        return new self('Master is not registered in the order category.');
    }

    public static function alreadyFinal(): self
    {
        return new self('Order is already in a final status.');
    }

    public static function invalidTransition(string $from, string $to): self
    {
        return new self("Invalid status transition: {$from} → {$to}.");
    }
}
