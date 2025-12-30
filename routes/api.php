<?php

use App\Http\Controllers\Library\BorrowingLibraryItemController;
use App\Http\Controllers\Library\LibraryItemsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Welfare\WelfareClaimController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::middleware('api.auth')->group(function () {
    Route::controller(UserController::class)->prefix('user')->group(function () {
        Route::post('fetchUserViaCnic', 'fetchUserViaCnic')->name('fetchUserViaCnic');
    });
    Route::post('/login', function (Request $request) {
        $request->validate([
            'email' => 'required|email',          // Change to 'cnic' if you use CNIC
            'password' => 'required|string',
        ]);

        // Find user by email (or change to 'cnic' => $request->cnic)
        $user = User::where('email', $request->email)->first();

        // Check if user exists and password is correct
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        // Create and return Sanctum token
        $token = $user->createToken('postman-test-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ], 200);

    });
    Route::controller(LibraryItemsController::class)->prefix('library-items')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::get('edit/{id}', 'edit')->name('edit');
        Route::post('store', 'store')->name('store');
        Route::post('update', 'update')->name('update');
        Route::get('delete/{id}', 'delete/{id}')->name('delete');
    });
    Route::controller(BorrowingLibraryItemController::class)->prefix('borrow')->group(function () {
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

require __DIR__.'/Auth.php';
require __DIR__.'/Committe.php';
require __DIR__.'/Complaint.php';
require __DIR__.'/Announcement.php';
require __DIR__.'/Admin.php';
require __DIR__.'/Finance.php';
