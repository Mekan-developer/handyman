<?php

namespace App\Exceptions;

use RuntimeException;

class MasterDisabledException extends RuntimeException
{
    public static function inactive(): self
    {
        return new self('Master account is disabled.');
    }

    public static function accessExpired(): self
    {
        return new self('Master access has expired.');
    }
}
