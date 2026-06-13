<?php

namespace App\Exceptions;

class OtpException extends ApiException
{
    public static function invalid(): self
    {
        return new self((string) __('api.otp.invalid'));
    }
}
