<?php

use App\Http\Controllers\NewbornController;
use Illuminate\Support\Facades\Route;

Route::get('/newborn', [NewbornController::class, 'index'])->name('newborn.index');
Route::post('/newborn', [NewbornController::class, 'store'])->name('newborn.store');
Route::get('/newborn/mothers', [NewbornController::class, 'mothers'])->name('newborn.mothers');
Route::get('/newborn/insemination', [NewbornController::class, 'insemination'])->name('newborn.insemination');
Route::get('/newborn/data', [NewbornController::class, 'data'])->name('newborn.data');

// Birth records (the calves list): one record per mother and calf number.
Route::prefix('/calves/{mother}/{n}')->where(['mother' => '[A-Za-z0-9]+', 'n' => '[1-3]'])->group(function () {
    Route::get('/', [NewbornController::class, 'show'])->name('calves.show');
    Route::get('/edit', [NewbornController::class, 'edit'])->name('calves.edit');
    Route::put('/', [NewbornController::class, 'update'])->name('calves.update');
    Route::delete('/', [NewbornController::class, 'destroy'])->name('calves.destroy');
});
