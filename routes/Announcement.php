<?php

use App\Http\Controllers\Api\Announcement\AnnouncementController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.auth')->group(function () {
    Route::controller(AnnouncementController::class)->prefix('announcements')->name('announcements.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::post('update/{id}', 'update')->name('update');
        Route::delete('delete/{id}', 'destroy')->name('destroy');
    });
});