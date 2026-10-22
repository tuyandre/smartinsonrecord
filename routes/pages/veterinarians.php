<?php

use App\Http\Controllers\VeterinarianController;
use Illuminate\Support\Facades\Route;

Route::get('/veterinarians', [VeterinarianController::class, 'index'])->name('veterinarians.index');
Route::get('/veterinarians/create', [VeterinarianController::class, 'create'])->name('veterinarians.create');
Route::post('/veterinarians', [VeterinarianController::class, 'store'])->name('veterinarians.store');
Route::get('/veterinarians/import', [VeterinarianController::class, 'importForm'])->name('veterinarians.import');
Route::post('/veterinarians/import', [VeterinarianController::class, 'import'])->name('veterinarians.import.store');
Route::get('/veterinarians/template', [VeterinarianController::class, 'template'])->name('veterinarians.template');
Route::get('/veterinarians/{veterinarian}', [VeterinarianController::class, 'show'])->whereNumber('veterinarian')->name('veterinarians.show');
Route::get('/veterinarians/{veterinarian}/edit', [VeterinarianController::class, 'edit'])->whereNumber('veterinarian')->name('veterinarians.edit');
Route::put('/veterinarians/{veterinarian}', [VeterinarianController::class, 'update'])->whereNumber('veterinarian')->name('veterinarians.update');
Route::patch('/veterinarians/{veterinarian}/status', [VeterinarianController::class, 'toggle'])->whereNumber('veterinarian')->name('veterinarians.toggle');
Route::delete('/veterinarians/{veterinarian}', [VeterinarianController::class, 'destroy'])->whereNumber('veterinarian')->name('veterinarians.destroy');
