<?php

use App\Http\Controllers\RecordsController;
use Illuminate\Support\Facades\Route;

foreach (['cows', 'inseminations', 'calves', 'pd'] as $name) {
    Route::get("/records/{$name}", [RecordsController::class, $name])->name("records.{$name}");
}
