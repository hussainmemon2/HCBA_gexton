<?php

use App\Http\Controllers\Api\Users\ElectionApplicationController;
use App\Http\Controllers\Api\Users\ElectionController;
use App\Http\Controllers\Api\Users\ElectionPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.auth')->prefix('elections')->group(function () {
    Route::controller(ElectionController::class)->group(function () {
        Route::get('/', 'index');

    });
    Route::controller(ElectionApplicationController::class)->group(function () {
        Route::post('/{election}/apply', 'apply');
        Route::post('/{election}/submit', 'submitApplication');
    });
    Route::controller(ElectionPaymentController::class)->group(function () {
        Route::post('/{election}/pay-application-fee', 'payApplicationFee');
        Route::post('/{election}/pay-submission-fee',  'paySubmissionFee');
    });
});
