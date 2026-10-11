<?php

use App\Http\Controllers\NewbornController;
use Illuminate\Support\Facades\Route;

Route::get('/newborn', [NewbornController::class, 'index'])->name('newborn.index');
Route::post('/newborn', [NewbornController::class, 'store'])->name('newborn.store');
Route::get('/newborn/insemination', [NewbornController::class, 'insemination'])->name('newborn.insemination');
Route::get('/newborn/data', [NewbornController::class, 'data'])->name('newborn.data');
