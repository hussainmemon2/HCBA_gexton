<?php

use App\Http\Controllers\Api\Users\ProfileController;
use App\Http\Controllers\Library\BorrowingLibraryItemController;
use App\Http\Controllers\Library\LibraryItemsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Welfare\WelfareClaimController;
use Illuminate\Support\Facades\Route;


Route::middleware('api.auth')->group(function () {
    Route::controller(UserController::class)->prefix('user')->group(function () {
        Route::post('fetchUserViaCnic', 'fetchUserViaCnic')->name('fetchUserViaCnic');
    });
    Route::controller(LibraryItemsController::class)->prefix('library-items')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::get('edit/{id}', 'edit')->name('edit');
        Route::post('store', 'store')->name('store');
        Route::post('update/{id}', 'update')->name('update');
        Route::get('delete/{id}', 'destroy')->name('delete');
    });
    Route::controller(BorrowingLibraryItemController::class)->middleware('verifyReservation')->prefix('borrow')->group(function () {
        Route::get('list/{bookID}', 'fetchBorrowHistory')->name('fetchBorrowHistory');
        Route::post('store', 'store')->name('store');
    });
    Route::controller(WelfareClaimController::class)->prefix('welfare-claims')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::get('show/{id}', 'show')->name('show');
        Route::post('add-remark/{id}', 'addRemark')->name('addRemark');
        Route::post('update-status/{id}', 'updateStatus')->name('updateStatus');
        Route::post('amount/{id}', 'updateAmount')->name('updateAmount');
    });
});

Route::middleware('api.auth')->group(function () {
    Route::controller(ProfileController::class)->prefix('profile')->group(function () {
        Route::get('/', 'Profile')->name('profile');
        Route::post('update', 'updateProfile')->name('updateProfile');
        Route::post('change-password', 'changePassword')->name('changePassword');
    });
});

require __DIR__ . '/Auth.php';
require __DIR__ . '/Committe.php';
require __DIR__ . '/Booking.php';
require __DIR__ . '/Auditorium.php';
require __DIR__ . '/Complaint.php';
require __DIR__ . '/Admin.php';
require __DIR__ . '/Finance.php';
require __DIR__ . '/NfcCard.php';
require __DIR__ . '/Election.php';
