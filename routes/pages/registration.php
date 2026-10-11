<?php

use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/registration', [RegistrationController::class, 'index'])->name('registration.index');
Route::post('/registration', [RegistrationController::class, 'store'])->name('registration.store');
Route::put('/registration/{eartagno}', [RegistrationController::class, 'update'])->whereNumber('eartagno')->name('registration.update');
Route::delete('/registration/{eartagno}', [RegistrationController::class, 'destroy'])->whereNumber('eartagno')->name('registration.destroy');
Route::post('/registration/check', [RegistrationController::class, 'check'])->name('registration.check');
Route::post('/registration/details', [RegistrationController::class, 'details'])->name('registration.details');
