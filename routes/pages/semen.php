<?php

use App\Http\Controllers\SemenController;
use Illuminate\Support\Facades\Route;

Route::get('/semen', [SemenController::class, 'index'])->name('semen.index');
Route::get('/semen/registration', [SemenController::class, 'create'])->name('semen.create');
Route::post('/semen/registration', [SemenController::class, 'store'])->name('semen.store');
Route::get('/semen/{semen}', [SemenController::class, 'show'])->whereNumber('semen')->name('semen.show');
Route::get('/semen/{semen}/edit', [SemenController::class, 'edit'])->whereNumber('semen')->name('semen.edit');
Route::put('/semen/{semen}', [SemenController::class, 'update'])->whereNumber('semen')->name('semen.update');
Route::delete('/semen/{semen}', [SemenController::class, 'destroy'])->whereNumber('semen')->name('semen.destroy');
