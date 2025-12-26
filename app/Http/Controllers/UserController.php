<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * grab user from cnic
     */
    public function fetchUserViaCnic(Request $request)
{
    // Validate: CNIC is required, exactly 13 digits, numeric only
    $request->validate([
        'cnic_number' => [
            'required',
            'digits:13',
            'regex:/^\d{13}$/',  // Ensures only digits, no spaces/dashes
        ],
    ]);

    // Clean the CNIC (remove any non-digits just in case)
    $cnic = preg_replace('/\D/', '', $request->cnic_number);

    // Find user by CNIC
    $user = User::where('cnic', $cnic)->first();

    // If user not found → return error
    if (!$user) {
        return response()->json([
            'status'  => 'error',
            'message' => 'User does not exist with this CNIC number.',
        ], 404);
    }

    // User found → return user data
    return response()->json([
        'status' => 'success',
        'message' => 'User found successfully.',
        'user'   => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'cnic'  => $user->cnic,
        ],
    ], 200);
}

   
}
