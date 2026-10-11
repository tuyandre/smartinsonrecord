<?php

use App\Http\Controllers\PdController;
use Illuminate\Support\Facades\Route;

Route::get('/pd', [PdController::class, 'index'])->name('pd.index');
Route::post('/pd/lookup', [PdController::class, 'lookup'])->name('pd.lookup');
Route::post('/pd', [PdController::class, 'store'])->name('pd.store');
