<?php

namespace App\Actions;

use App\Models\Master;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RequestMasterOtpAction
{
    public function handle(Master $master): void
    {
        $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        Cache::put("master_otp:{$master->phone}", $code, now()->addMinutes(5));

        // TODO: send via SMS gateway
        Log::info("OTP for master {$master->phone}: {$code}");
    }
}
