<?php

use App\Http\Controllers\Api\Admin\UsersController;
use Illuminate\Support\Facades\Route;


Route::middleware(['api.auth' , 'apiRole:admin'])->prefix('admin')->group(function () {
    Route::controller(UsersController::class)->prefix('users')->group(function () {
        Route::get('/' , 'index');
        Route::post('/create' , 'store');
        Route::post('/otp/send' , 'sendotp');
        Route::post('/otp/verify' , 'verifyotp');
        Route::get('/view/{id}' , 'view');
        Route::post('/role/assign/{id}' , 'assignrole');
        Route::post('/account/status/{id}' , 'statuschange');
    });
});
