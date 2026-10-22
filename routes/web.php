<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'landing'])->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    if (config('auth.signup_enabled')) {
        Route::get('/sign-up', [AuthController::class, 'showSignUp'])->name('sign-up');
        Route::post('/sign-up', [AuthController::class, 'signUp']);
        Route::post('/sign-up/lookup', [AuthController::class, 'lookupVet'])->middleware('throttle:30,1')->name('sign-up.lookup');
    }
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'permission'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/locations', LocationController::class)->name('locations');

    // One route file per ported page (routes/pages/*.php), all behind auth.
    foreach (glob(base_path('routes/pages/*.php')) as $file) {
        require $file;
    }
});
