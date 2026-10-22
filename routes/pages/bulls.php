<?php

use App\Http\Controllers\BullController;
use Illuminate\Support\Facades\Route;

$tag = '(?!registration$)[A-Za-z0-9]+';

Route::get('/bulls', [BullController::class, 'index'])->name('bulls.index');
Route::get('/bulls/registration', [BullController::class, 'create'])->name('bulls.create');
Route::post('/bulls/registration', [BullController::class, 'store'])->name('bulls.store');
Route::get('/bulls/{eartagno}', [BullController::class, 'show'])->where('eartagno', $tag)->name('bulls.show');
Route::get('/bulls/{eartagno}/edit', [BullController::class, 'edit'])->where('eartagno', $tag)->name('bulls.edit');
Route::put('/bulls/{eartagno}', [BullController::class, 'update'])->where('eartagno', $tag)->name('bulls.update');
Route::delete('/bulls/{eartagno}', [BullController::class, 'destroy'])->where('eartagno', $tag)->name('bulls.destroy');
