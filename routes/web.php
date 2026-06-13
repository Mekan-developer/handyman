<?php

use App\Http\Controllers\BannerController;
use App\Http\Controllers\CategoryContentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OblastController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\TilesController;
use Illuminate\Support\Facades\Route;

Route::get('/tiles/{file}', [TilesController::class, 'serve'])->where('file', '.+\.pmtiles');

Route::post('/locale/{locale}', function (string $locale) {
    $supported = ['ru', 'tk'];
    if (in_array($locale, $supported, strict: true)) {
        session(['locale' => $locale]);
    }

    return back();
})->name('locale.set');

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('oblasts', OblastController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('regions', RegionController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('cities', CityController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('categories/{category}/content', [CategoryContentController::class, 'upsert'])->name('categories.content.upsert');
    Route::get('masters/map', [MasterController::class, 'map'])->name('masters.map');
    Route::get('masters/{master}/trajectory', [MasterController::class, 'trajectory'])->name('masters.trajectory');
    Route::post('masters/{master}/reset-balance', [MasterController::class, 'resetBalance'])->name('masters.reset-balance');
    Route::resource('masters', MasterController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('banners', BannerController::class)->only(['index', 'store', 'destroy']);
    Route::post('banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
    Route::post('banners/{banner}/toggle', [BannerController::class, 'toggle'])->name('banners.toggle');

    Route::post('clients/{client}/toggle-block', [ClientController::class, 'toggleBlock'])->name('clients.toggle-block');
    Route::resource('clients', ClientController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');

    Route::resource('orders', OrderController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::post('orders/{order}/assign', [OrderController::class, 'assign'])->name('orders.assign');
    Route::post('orders/{order}/price', [OrderController::class, 'setPrice'])->name('orders.set-price');
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('orders/{order}/master-trajectory', [OrderController::class, 'masterTrajectoryForOrder'])->name('orders.master-trajectory');

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('{id}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::post('read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
        Route::delete('{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/', [NotificationController::class, 'destroyAll'])->name('destroy-all');
    });
});

require __DIR__.'/auth.php';
