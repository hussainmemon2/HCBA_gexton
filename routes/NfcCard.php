<?php

use App\Http\Controllers\Api\Users\NfcCardController;
use App\Http\Controllers\Api\Users\NfcCardRequestController;
use Illuminate\Support\Facades\Route;



Route::middleware('api.auth')->group(function () {
    Route::controller(NfcCardRequestController::class)->prefix('nfc')->group(function () {
    Route::get('/requests','myRequests');
    Route::post('/requests','store');
    });
Route::controller(NfcCardController::class)->prefix('nfc')->group(function () {
    Route::get('/cards',  'myCards');
    Route::post('/cards/{card}/toggle',  'toggleCardStatus');
});
});