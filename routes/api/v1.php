<?php

use App\Http\Controllers\Api\V1\MasterAuthController;
use App\Http\Controllers\Api\V1\MasterLocationController;
use App\Http\Controllers\Api\V1\MasterOrderController;
use App\Http\Controllers\Api\V1\MasterProfileController;
use App\Http\Controllers\Api\V1\MasterTaskController;
use Illuminate\Support\Facades\Route;

/*
 * Mobile API v1 — used by Master mobile app (Flutter).
 * Auth: OTP → Sanctum personal access token (Bearer).
 */

Route::prefix('master')->group(function () {

    // Public — location ping (temporary open auth until OTP flow stabilises)
    Route::post('{master}/location', [MasterLocationController::class, 'store'])
        ->name('api.v1.master.location.store');

    // Auth
    Route::prefix('auth')->name('api.v1.master.auth.')->group(function () {
        Route::post('request-otp', [MasterAuthController::class, 'requestOtp'])->name('request-otp');
        Route::post('verify-otp', [MasterAuthController::class, 'verifyOtp'])->name('verify-otp');
        Route::post('logout', [MasterAuthController::class, 'logout'])
            ->middleware('auth:sanctum')
            ->name('logout');
    });

    // Protected — requires Sanctum token
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('me', [MasterProfileController::class, 'show'])->name('api.v1.master.me');

        Route::prefix('orders')->name('api.v1.master.orders.')->group(function () {
            Route::get('/', [MasterOrderController::class, 'index'])->name('index');
            Route::get('{order}', [MasterOrderController::class, 'show'])->name('show');
            Route::post('{order}/start', [MasterOrderController::class, 'start'])->name('start');
            Route::post('{order}/complete', [MasterOrderController::class, 'complete'])->name('complete');

            Route::prefix('{order}/tasks')->name('tasks.')->group(function () {
                Route::post('/', [MasterTaskController::class, 'store'])->name('store');
                Route::post('{task}/photo', [MasterTaskController::class, 'uploadPhoto'])->name('photo');
            });
        });
    });
});
