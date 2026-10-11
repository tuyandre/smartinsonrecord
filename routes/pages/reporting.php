<?php

use App\Http\Controllers\ReportingController;
use Illuminate\Support\Facades\Route;

Route::get('/reporting', [ReportingController::class, 'index'])->name('reporting.index');
