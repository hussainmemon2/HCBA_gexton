<?php

use App\Http\Controllers\Api\Authentication\LoginController;
use App\Http\Controllers\Api\Authentication\ResetController;
use App\Http\Controllers\Api\Authentication\SignUpController;
use Illuminate\Support\Facades\Route;


Route::controller(LoginController::class)->group(function (){
    Route::post('/login' , 'login');
    Route::post('/login/otp/send' , 'sendotp');
    Route::post('/login/otp/verify' , 'verifyotp');
});

Route::controller(SignUpController::class)->group(function (){
    Route::post('/register' , 'register');
    Route::post('/register/otp/send' , 'sendotp');
    Route::post('/register/otp/verify' , 'verifyotp');
});

Route::controller(ResetController::class)->group(function (){
    Route::post('/password/forgot' , 'sendResetPasswordOtp');
    Route::post('/forgot/otp/verify' , 'verifyResetPasswordOtp');
    Route::post('/password/reset' , 'resetPassword');
});
