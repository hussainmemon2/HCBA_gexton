<?php

use App\Http\Controllers\Api\Public\PublicApisController;
use Illuminate\Support\Facades\Route;

Route::controller(PublicApisController::class)->prefix('public')->group(function () {
    Route::get('library-items', 'libraryItems')->name('libraryItems');
    Route::get('fetch-active-election', 'fetchActiveElection')->name('fetchActiveElection');
});
