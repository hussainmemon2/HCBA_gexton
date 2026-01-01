<?php

use App\Http\Controllers\Api\Committe\CommitteeController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.auth' )->controller(CommitteeController::class)->prefix('committee')->group(function (){
    Route::get('/' , 'index');
    Route::get('/view/{id}' , 'view');
    Route::get('/available-users' , 'availableUsers');
    Route::post('/create' , 'store')->middleware('apiRole:admin');
    Route::post('/update/{id}' , 'update');
});
