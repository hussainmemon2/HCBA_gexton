<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Complaint\ComplaintController;

Route::middleware('api.auth')->controller(ComplaintController::class)->prefix('complaint')->group(function (){
    Route::get('/' , 'index');
    Route::post('/create' , 'store');
    Route::get('/committes' , 'committes');
});

// test auth token 
// 1|rfuKU56YUXz6b4Fh4q6jWWNXW9ZYu9fnySS0miUO04c99abd