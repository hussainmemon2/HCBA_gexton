<?php

use App\Http\Controllers\Api\Auditorium\AuditoriumController;
use Illuminate\Support\Facades\Route;


Route::middleware(['api.auth', 'apiRole:admin'])->controller(AuditoriumController::class)->prefix('auditorium')->name('auditorium.')->group(function () {
    Route::get('index', 'index')->name('index');
    Route::get('show/{id}', 'edit')->name('edit');
    Route::post('store', 'store')->name('store');
    Route::post('update/{id}', 'update')->name('update');
    Route::delete('delete/{id}', 'destroy')->name('delete');
});
