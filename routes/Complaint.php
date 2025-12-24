<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Complaint\ComplaintController;

Route::middleware('api.auth')->controller(ComplaintController::class)->prefix('complaint')->group(function (){
    Route::get('/' , 'index');
    Route::post('/create' , 'store');
    Route::get('/committes' , 'committes');
    Route::get('/view/{id}' , 'show');
    Route::post('/add-remark/{id}' , 'addRemark');
    Route::post('/close/{id}' , 'close');
    Route::post('/satisfaction-feedback/{id}' , 'respondSatisfaction');
});
