<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('cities', CityController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('masters/map', [MasterController::class, 'map'])->name('masters.map');
    Route::get('masters/{master}/trajectory', [MasterController::class, 'trajectory'])->name('masters.trajectory');
    Route::post('masters/{master}/reset-balance', [MasterController::class, 'resetBalance'])->name('masters.reset-balance');
    Route::resource('masters', MasterController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('orders', OrderController::class)->only(['index', 'show', 'store', 'destroy']);
    Route::post('orders/{order}/assign', [OrderController::class, 'assign'])->name('orders.assign');
    Route::post('orders/{order}/price', [OrderController::class, 'setPrice'])->name('orders.set-price');
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('orders/{order}/master-trajectory', [OrderController::class, 'masterTrajectoryForOrder'])->name('orders.master-trajectory');
});

require __DIR__.'/auth.php';
