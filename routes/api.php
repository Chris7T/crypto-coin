<?php

use App\Http\Controllers\CurrentCryptoCoinPriceController;
use App\Http\Controllers\EstimatedCryptoCoinPriceInDatetimeController;
use Illuminate\Support\Facades\Route;

Route::prefix('crypto-coin')->name('crypto-coin.')->group(function () {
    Route::get('current', CurrentCryptoCoinPriceController::class)
        ->name('current');
    Route::get('previous', EstimatedCryptoCoinPriceInDatetimeController::class)
        ->name('previous');
});
