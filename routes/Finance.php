<?php

use App\Http\Controllers\Api\Finance\AccountController;
use App\Http\Controllers\Api\Finance\AccountGroupController;
use App\Http\Controllers\Api\Finance\ChequebookController;
use Illuminate\Support\Facades\Route;


Route::middleware(['api.auth' , 'apiRole:admin,treasurer'])->group(function () {
   Route::controller(AccountGroupController::class)->group(function () {
       Route::get('/account-groups', 'index');
       Route::post('/account-groups', 'store');
       Route::post('/account-groups/update/{id}', 'update');
       Route::post('/account-groups/delete/{id}', 'destroy');
   });

   Route::controller(AccountController::class)->group(function () {
       Route::get('/accounts', 'index');
       Route::post('/accounts', 'store');
       Route::get('/accounts/{id}', 'show');
       Route::post('/accounts/update/{id}', 'update');
       Route::post('/accounts/delete/{id}', 'destroy');
       Route::post('/accounts/toggle-status/{id}', 'toggleStatus');
   });

   Route::controller(ChequebookController::class)->group(function () {
       Route::get('/checkbooks', 'index');
       Route::get('/checkbooks/banks', 'bankAccountsDropdown');
       Route::post('/checkbooks', 'store');
       Route::get('/checkbooks/{id}', 'show');
       Route::post('/checkbooks/update/{id}', 'update');
       Route::post('/checkbooks/delete/{id}', 'destroy');

   });
});
