<?php

use App\Http\Controllers\Api\Admin\FeesController;
use App\Http\Controllers\Api\Admin\FinanceController;
use App\Http\Controllers\Api\Admin\UsersController;
use Illuminate\Support\Facades\Route;


Route::middleware(['api.auth', 'apiRole:admin,president,vice-president,general-secretary,joint-secretary,lib`rary-secretary'])->prefix('admin')->group(function () {
    Route::controller(UsersController::class)->prefix('users')->group(function () {
        Route::get('/' , 'index');
        Route::post('/create' , 'store');
        Route::post('/otp/send' , 'sendotp');
        Route::post('/otp/verify' , 'verifyotp');
        Route::get('/view/{id}' , 'view');
        Route::post('/role/assign/{id}' , 'assignrole');
        Route::post('/account/status/{id}' , 'statuschange');
    });
    Route::controller(FinanceController::class)->prefix('finance')->group(function () {
        Route::get('/' , 'ledger');
        Route::post('/create' , 'store');
    });
    Route::controller(FeesController::class)->prefix('fees-settings')->group(function () {
        Route::get('/annual-fee' , 'getAnnualFee');
        Route::post('/annual-fee' , 'updateAnnualFee');
    });
});
