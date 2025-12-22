<?php

namespace App\Http\Controllers\Api\Authentication;

use App\Http\Controllers\Controller;
use App\Mail\Auth\ResetPasswordOtpMail;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ResetController extends Controller
{
    public function sendResetPasswordOtp(Request $request)
    {
    $validator = Validator::make($request->all(), [
    'cnic' => 'required|string|exists:users,cnic',
    ]);
    if ($validator->fails()) {
    return response()->json([
        'status' => 'error',
        'errors' => $validator->errors()
    ], 422);
    }
    $user = User::where('cnic', $request->cnic)->first();
    if (!$user->email_verified) {
    return response()->json([
        'status' => 'error',
        'message' => 'Email not verified.',
        "email"=>$user->email
    ], 403);
    }
    $recentOtp = Otp::where('user_id', $user->id)
    ->where('type', 'password_reset')
    ->where('created_at', '>=', now()->subMinutes(5))
    ->whereNull('used_at')
    ->first();
    if ($recentOtp) {
    return response()->json([
        'status' => 'error',
        'message' => 'OTP already sent. Please wait 5 minutes.'
    ], 429);
    }

    Otp::where('user_id', $user->id)
    ->where('type', 'password_reset')
    ->whereNull('used_at')
    ->update(['used_at' => now()]);
    $otpCode = rand(100000, 999999);
    Otp::create([
    'user_id'    => $user->id,
    'identifier' => $user->email,
    'otp'        => Hash::make($otpCode),
    'type'       => 'password_reset',
    'expires_at' => now()->addMinutes(5),
    ]);

    Mail::to($user->email)
    ->later(now()->addMinute(), new ResetPasswordOtpMail($otpCode, $user->name));

    return response()->json([
    'status' => 'success',
    'message' => 'Password reset OTP sent to your email.',
    "email"   =>$user->email
    ]);
    }
    public function verifyResetPasswordOtp(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
        'otp'   => 'required|digits:6',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors()
        ], 422);
    }

    $user = User::where('email', $request->email)->first();

    $otp = Otp::where('user_id', $user->id)
        ->where('type', 'password_reset')
        ->whereNull('used_at')
        ->latest()
        ->first();

    if (!$otp || !Hash::check($request->otp, $otp->otp)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid OTP.'
        ], 400);
    }

    if (now()->greaterThan($otp->expires_at)) {
        return response()->json([
            'status' => 'error',
            'message' => 'OTP expired.'
        ], 400);
    }
    $otp->update([
        'used_at' => now()
    ]);
    return response()->json([
        'status' => 'success',
        'message' => 'OTP verified. You may now reset your password.'
    ]);
    
    }
    public function resetPassword(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'email'        => 'required|email|exists:users,email',
        'new_password' => 'required|string|min:6',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors()
        ], 422);
    }
    $user = User::where('email', $request->email)->first();
    // Safety: invalidate all login OTPs & tokens
    Otp::where('user_id', $user->id)
        ->where('type', 'login')
        ->whereNull('used_at')
        ->update(['used_at' => now()]);
        $user->tokens()->delete();
        $user->update([
            'password' => $request->new_password
        ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Password reset successfully.'
    ]);
    }

}
