<?php

use App\Http\Controllers\Api\V1\Client\ClientAuthController;
use App\Http\Controllers\Api\V1\Client\ClientCatalogController;
use App\Http\Controllers\Api\V1\Client\ClientOrderController;
use App\Http\Controllers\Api\V1\Client\ClientProfileController;
use App\Http\Controllers\Api\V1\MasterAuthController;
use App\Http\Controllers\Api\V1\MasterLocationController;
use App\Http\Controllers\Api\V1\MasterOrderController;
use App\Http\Controllers\Api\V1\MasterProfileController;
use App\Http\Controllers\Api\V1\MasterTaskController;
use Illuminate\Support\Facades\Route;

/*
 * Mobile API v1 — used by Master and Client mobile apps (Flutter).
 * Auth: OTP → Sanctum personal access token (Bearer).
 *   - Master token name: `mobile`        (require middleware: ensure.master)
 *   - Client token name: `mobile-client` (require middleware: ensure.client)
 */

Route::prefix('master')->group(function () {

    // Public — location ping (temporary open auth until OTP flow stabilises)
    Route::post('{master}/location', [MasterLocationController::class, 'store'])->name('api.v1.master.location.store');

    // Auth
    Route::prefix('auth')->name('api.v1.master.auth.')->group(function () {
        Route::post('request-otp', [MasterAuthController::class, 'requestOtp'])->name('request-otp');
        Route::post('verify-otp', [MasterAuthController::class, 'verifyOtp'])->name('verify-otp');
        Route::post('logout', [MasterAuthController::class, 'logout'])
            ->middleware(['auth:sanctum', 'ensure.master'])
            ->name('logout');
    });

    // Protected — requires Sanctum token + master tokenable
    Route::middleware(['auth:sanctum', 'ensure.master'])->group(function () {

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

Route::prefix('client')->group(function () {

    // Public catalog
    Route::get('oblasts', [ClientCatalogController::class, 'oblasts'])->name('api.v1.client.oblasts');
    Route::get('regions', [ClientCatalogController::class, 'regions'])->name('api.v1.client.regions');
    Route::get('cities', [ClientCatalogController::class, 'cities'])->name('api.v1.client.cities');
    Route::get('categories', [ClientCatalogController::class, 'categories'])->name('api.v1.client.categories');
    Route::get('categories/{category}/content', [ClientCatalogController::class, 'categoryContent'])->name('api.v1.client.categories.content');
    Route::get('banners', [ClientCatalogController::class, 'banners'])->name('api.v1.client.banners');

    // Auth
    Route::prefix('auth')->name('api.v1.client.auth.')->group(function () {
        Route::post('request-otp', [ClientAuthController::class, 'requestOtp'])->name('request-otp');
        Route::post('verify-otp', [ClientAuthController::class, 'verifyOtp'])->name('verify-otp');

        // Require the token issued by verify-otp.
        Route::middleware(['auth:sanctum', 'ensure.client'])->group(function () {
            Route::post('complete-registration', [ClientAuthController::class, 'completeRegistration'])->name('complete-registration');
            Route::post('logout', [ClientAuthController::class, 'logout'])->name('logout');
        });
    });

    // Protected — requires Sanctum token + client tokenable
    Route::middleware(['auth:sanctum', 'ensure.client'])->group(function () {

        Route::get('me', [ClientProfileController::class, 'show'])->name('api.v1.client.me');
        Route::patch('me', [ClientProfileController::class, 'update'])->name('api.v1.client.me.update');

        Route::prefix('orders')->name('api.v1.client.orders.')->group(function () {
            Route::get('/', [ClientOrderController::class, 'index'])->name('index');
            Route::get('{order}', [ClientOrderController::class, 'show'])->name('show');
            Route::post('/', [ClientOrderController::class, 'store'])->name('store');
            Route::post('{order}/cancel', [ClientOrderController::class, 'cancel'])->name('cancel');
        });
    });
});
