<?php

use App\Http\Controllers\Api\Booking\BookingControlller;
use Illuminate\Support\Facades\Route;

Route::middleware('api.auth')->controller(BookingControlller::class)->prefix('bookings')->name('bookings.')->group(function () {
    Route::get('index', 'index')->name('index');
    Route::post('store', 'store')->name('store');
    Route::post('update/{id}', 'update')->name('update');
    Route::delete('delete/{id}', 'destroy')->name('delete');
    Route::middleware('apiRole:admin')->group(function () {
        Route::post('status/{id}', 'updateStatus')->name('updateStatus');
    });
});
