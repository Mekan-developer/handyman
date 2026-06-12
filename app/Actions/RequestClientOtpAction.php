<?php

namespace App\Actions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RequestClientOtpAction
{
    public function handle(string $phone): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put("client_otp:{$phone}", $code, now()->addMinutes(5));

        // TODO: send via SMS gateway
        Log::info("OTP for client {$phone}: {$code}");
    }
}
