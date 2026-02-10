<?php

use App\Http\Controllers\Api\Admin\ElectionController;
use App\Http\Controllers\Api\Admin\ElectionPositionController;
use App\Http\Controllers\Api\Admin\ElectionReviewController;
use App\Http\Controllers\Api\Admin\FeesController;
use App\Http\Controllers\Api\Admin\FinanceController;
use App\Http\Controllers\Api\Admin\NfcCardController;
use App\Http\Controllers\Api\Admin\NfcCardRequestController;
use App\Http\Controllers\Api\Admin\StickerController;
use App\Http\Controllers\Api\Admin\UsersController;
use Illuminate\Support\Facades\Route;

Route::controller(UsersController::class)->group(function () {
    Route::get('fetchAdvocateData/{reg_no}', 'fetchAdvocateData');
});
Route::middleware(['api.auth', 'apiRole:admin,president,vice-president,general-secretary,joint-secretary,lib`rary-secretary'])->prefix('admin')->group(function () {
    Route::controller(UsersController::class)->prefix('users')->group(function () {
        Route::post('makeDuesPaid', 'setDuesPaid');
        Route::get('/', 'index');
        Route::post('/create', 'store');
        Route::post('/otp/send', 'sendotp');
        Route::post('/otp/verify', 'verifyotp');
        Route::get('/view/{id}', 'view');
        Route::post('/role/assign/{id}', 'assignrole');
        Route::post('/account/status/{id}', 'statuschange');
    });
    Route::controller(FinanceController::class)->prefix('finance')->group(function () {
        Route::get('/', 'ledger');
        Route::post('/create', 'store');
    });
    Route::controller(FeesController::class)->prefix('fees-settings')->group(function () {
        Route::get('/annual-fee', 'getAnnualFee');
        Route::post('/annual-fee', 'updateAnnualFee');
    });

    Route::controller(StickerController::class)->prefix('stickers')->group(function () {
        Route::post('/create', 'store');
        Route::get('/view/{id}', 'show');
        Route::post('/update/{id}', 'update');
        Route::post('/delete/{id}', 'destroy');
    });
    Route::controller(NfcCardController::class)
        ->prefix('nfc')
        ->group(function () {

            Route::get('/cards', 'index');
            Route::get('/cards/user/{id}', 'cardsByUser');
            Route::post('/assign', 'assign');
        });

    Route::controller(NfcCardRequestController::class)
        ->prefix('nfc-requests')
        ->group(function () {
            Route::get('/', 'index');
            Route::post('/{request}/approve', 'approve');
            Route::post('/{request}/reject', 'reject');
        });
});
Route::middleware(['api.auth', 'apiRole:admin'])->prefix('admin')->group(function () {
    Route::controller(ElectionController::class)->prefix('elections')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::post('/{id}/disable', 'disable');
        Route::post('/{id}/enable', 'enable');

    });
    Route::controller(ElectionPositionController::class)->prefix('elections')->group(function () {
        Route::post('/{electionId}/positions', 'store');
        Route::post('/positions/{id}', 'update');
    });
});
Route::middleware(['api.auth', 'apiRole:admin'])->prefix('admin/elections/{election}')->group(function () {
    Route::controller(ElectionReviewController::class)->group(function () {
        Route::get('/applications', 'listApplications');
        Route::post('/applications/{application}/approve', 'approve');
        Route::post('/applications/{application}/reject', 'reject');
    });
});
