<?php

use App\Http\Controllers\ReportingController;
use Illuminate\Support\Facades\Route;

Route::get('/reporting', [ReportingController::class, 'index'])->name('reporting.index');
Route::get('/reporting/animals/{kind}', [ReportingController::class, 'animals'])->where('kind', 'cows|bulls')->name('reporting.animals');
