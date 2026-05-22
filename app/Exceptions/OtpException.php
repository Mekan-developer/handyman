<?php

namespace App\Exceptions;

use RuntimeException;

class OtpException extends RuntimeException
{
    public static function invalid(): self
    {
        return new self('Invalid or expired OTP code.');
    }
}
