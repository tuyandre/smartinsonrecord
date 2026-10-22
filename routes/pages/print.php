<?php

use App\Http\Controllers\PrintController;
use Illuminate\Support\Facades\Route;

Route::get('/print/insemination/{eartagno}', [PrintController::class, 'insemination'])->where('eartagno', '[A-Za-z0-9]+')->name('print.insemination');
Route::get('/print/calf/{mother}/{n}', [PrintController::class, 'calf'])->where(['mother' => '[A-Za-z0-9]+', 'n' => '[1-3]'])->name('print.calf');
