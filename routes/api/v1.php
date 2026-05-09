<?php

use App\Http\Controllers\Api\V1\MasterLocationController;
use Illuminate\Support\Facades\Route;

/*
 * Mobile API v1 — currently used by:
 * - Master mobile app (Flutter): location pings
 * Auth: temporarily open by master_id; will switch to Sanctum tokens once
 * the master OTP login flow is implemented.
 */

Route::prefix('master')->group(function () {
    Route::post('{master}/location', [MasterLocationController::class, 'store'])
        ->name('api.v1.master.location.store');
});
