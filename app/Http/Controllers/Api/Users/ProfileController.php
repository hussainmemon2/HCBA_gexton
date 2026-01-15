<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
   function Profile(Request $request){
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ]);
   }
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $emailChanged = false;

        $validator = Validator::make($request->all(), [
            'present_address' => 'required|string',
            'office_address'  => 'nullable|string',
            'email'           => 'required|email|unique:users,email,' . $user->id,
            'phone'           => 'required|string|max:20|unique:users,phone,' . $user->id,
            'passport_image'  => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldPassportPath = $user->passport_image;
        $directory = $oldPassportPath
            ? dirname($oldPassportPath)
            : 'users/files/default';

        $fullPath = public_path($directory);

        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        if ($request->hasFile('passport_image')) {

            if ($oldPassportPath && file_exists(public_path($oldPassportPath))) {
                unlink(public_path($oldPassportPath));
            }

            $name = 'passport_' . time() . '.' . $request->passport_image->extension();
            $request->passport_image->move($fullPath, $name);

            $user->passport_image = $directory . '/' . $name;
        }

        if ($request->email !== $user->email) {
            $user->temp_email = $request->email;
            $user->email_verified = false;
            $user->email_verified_at = null;
            $emailChanged = true;
        }

        $user->present_address = $request->present_address;
        $user->office_address  = $request->office_address;
        $user->phone           = $request->phone;

        $user->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully.',
            'email_changed' => $emailChanged,
            'data' => $user
        ], 200);
    }



    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>'error','errors'=>$validator->errors()],422);
        }

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status'=>'error',
                'message'=>'Old password is incorrect'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'status'=>'success',
            'message'=>'Password updated successfully'
        ]);
    }

}
