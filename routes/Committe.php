<?php

use App\Http\Controllers\Api\Committe\CommitteeController;
use Illuminate\Support\Facades\Route;

Route::controller(CommitteeController::class)->prefix('committe')->group(function (){
    Route::get('/available-users' , 'availableUsers');
    Route::post('/create' , 'store');
});