<?php

namespace App\Actions;

use App\Exceptions\OtpException;
use App\Models\Master;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\NewAccessToken;

class VerifyMasterOtpAction
{
    public function handle(Master $master, string $code): NewAccessToken
    {
        $cached = Cache::get("master_otp:{$master->phone}");

        if ($cached === null || $cached !== $code) {
            throw OtpException::invalid();
        }

        Cache::forget("master_otp:{$master->phone}");

        $master->tokens()->where('name', 'mobile')->delete();

        return $master->createToken('mobile');
    }
}
