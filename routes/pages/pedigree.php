<?php

use App\Http\Controllers\PedigreeController;
use Illuminate\Support\Facades\Route;

Route::get('/pedigree', [PedigreeController::class, 'index'])->name('pedigree.index');
