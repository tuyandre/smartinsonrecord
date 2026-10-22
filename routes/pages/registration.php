<?php

use App\Http\Controllers\CowController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/cows/registration', [RegistrationController::class, 'index'])->name('registration.index');
Route::post('/cows/registration', [RegistrationController::class, 'store'])->name('registration.store');
Route::put('/cows/registration/{eartagno}', [RegistrationController::class, 'update'])->whereAlphaNumeric('eartagno')->name('registration.update');
Route::delete('/cows/registration/{eartagno}', [RegistrationController::class, 'destroy'])->whereAlphaNumeric('eartagno')->name('registration.destroy');
Route::post('/cows/registration/check', [RegistrationController::class, 'check'])->name('registration.check');
Route::post('/cows/registration/details', [RegistrationController::class, 'details'])->name('registration.details');

// Cow detail and edit pages (registered after /cows/registration so that path wins).
Route::get('/cows/{eartagno}', [CowController::class, 'show'])->where('eartagno', '(?!registration$)[A-Za-z0-9]+')->name('cows.show');
Route::get('/cows/{eartagno}/edit', [CowController::class, 'edit'])->where('eartagno', '[A-Za-z0-9]+')->name('cows.edit');
