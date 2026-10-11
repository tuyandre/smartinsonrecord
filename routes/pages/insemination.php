<?php

use App\Http\Controllers\InseminationController;
use Illuminate\Support\Facades\Route;

Route::get('/insemination', [InseminationController::class, 'index'])->name('insemination.index');
Route::post('/insemination', [InseminationController::class, 'store'])->name('insemination.store');
Route::get('/insemination/last-mating', [InseminationController::class, 'lastMating'])->name('insemination.last-mating');
