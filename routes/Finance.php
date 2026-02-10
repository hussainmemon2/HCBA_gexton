<?php

use App\Http\Controllers\Api\Finance\AccountController;
use App\Http\Controllers\Api\Finance\AccountGroupController;
use App\Http\Controllers\Api\Finance\CheckbookController;
use App\Http\Controllers\Api\Finance\ChequebookController;
use App\Http\Controllers\Api\Finance\VendorController;
use App\Http\Controllers\Api\Finance\VoucherController;
use App\Http\Controllers\Api\Finance\VoucherTypeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Finance\ReportController;


Route::middleware(['api.auth' , 'apiRole:admin,treasurer'])->group(function () {

    Route::controller(AccountController::class)->group(function () {
        Route::get('/accounts', 'index');
        Route::post('/accounts', 'store');
        Route::get('/accounts/{id}', 'show');
        Route::post('/accounts/update/{id}', 'update');
        Route::post('/accounts/delete/{id}', 'destroy');
        Route::post('/accounts/toggle-status/{id}', 'toggleStatus');
    });

    Route::controller(CheckbookController::class)->group(function (){
        Route::get('/checkbooks', 'index');
        Route::post('/checkbooks', 'store');
        Route::get('/checkbooks/{id}', 'show');
        Route::post('/checkbooks/update/{id}', 'update');
        Route::post('/checkbooks/delete/{id}', 'destroy');
        Route::get('/checkbook/banks', 'bankAccounts');


    });
    Route::controller(VoucherController::class)->group(function (){
        Route::get('/vouchers', 'index');
        Route::post('/vouchers', 'store');
        Route::get('/vouchers/{id}', 'show');
        Route::get('/entities', 'getEntitiesBySubtype'); 
        Route::get('/asset-accounts', 'getAssetAccountsByPaymentMethod');
        Route::get('/bank-checkbooks', 'getCheckbooksByBank');
        Route::get('/unused-cheques', 'getUnusedChequesByCheckbook');
    });
    Route::controller(VendorController::class)->group(function (){
        Route::get('/vendors', 'index');
        Route::post('/vendors', 'store');
        Route::get('/vendors/{id}', 'show');
        Route::post('/vendors/update/{id}', 'update');
        Route::post('/vendors/delete/{id}', 'destroy');
    });

});


Route::middleware(['api.auth' , 'apiRole:admin'])->controller(VoucherController::class)->group(function (){
    Route::post('/vouchers/{id}/approve', 'approve');
    Route::post('/vouchers/{id}/reject', 'reject');
 
});

Route::middleware(['api.auth', 'apiRole:admin,treasurer'])->group(function () {
    Route::get('/reports/trial-balance', [ReportController::class, 'trialBalance']);
    Route::get('/reports/ledger', [ReportController::class, 'ledger']);
    Route::get('/reports/cash-bank-book', [ReportController::class, 'cashBankBook']);
    // Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss']);
    Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet']);
});

