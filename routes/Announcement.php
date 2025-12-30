<?php

use App\Http\Controllers\Api\Announcement\AnnouncementController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.auth')->group(function () {
    Route::controller(AnnouncementController::class)->prefix('announcements')->group(function () {
        Route::get('index', 'index')->name('announcements.index');
        Route::post('store', 'store')->name('announcements.store');
        // Route::get('show/{id}', 'show')->name('announcements.show');
        Route::post('update/{id}', 'update')->name('announcements.update');
        Route::delete('delete/{id}', 'destroy')->name('announcements.destroy');
    });
});