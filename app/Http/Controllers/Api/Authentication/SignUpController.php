<?php

namespace App\Http\Controllers\Api\Authentication;

use App\Http\Controllers\Controller;
use App\Mail\Auth\VerifyEmail;
use App\Models\NfcCard;
use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\ValidAdvocate;
use Illuminate\Support\Facades\Mail;

class SignUpController extends Controller
{
    public function register(Request $request)
    {
     $validator = Validator::make($request->all(), [
        'proposer_name' => 'required|string|max:100',
        'seconder_name' => 'required|string|max:100',
        'name' => 'required|string|max:100',
        'guardian_name' => 'required|string|max:100',
        'date_of_birth' => 'required|date',
        'gender' => 'required|in:male,female,other',
        'caste' => 'required|string|max:100',
        'cnic' => 'required|digits:13|unique:users,cnic',
        'bar_license_number' => 'required|string|unique:users,bar_license_number',
        // Addresses
        'present_address' => 'required|string',
        'office_address' => 'nullable|string',
        // Enrollment
        'date_of_enrollment_as_advocate' => 'required|date',
        'date_of_enrollment_as_advocate_high_court' => 'nullable|date',
        'district_bar_member' => 'required|string|max:150',
        'other_bar_member' => 'required|string|max:150',
        // Contact
        'email' => 'required|email|unique:users,email',
        'phone' => 'required|string|unique:users,phone|max:20',
        // Auth
        'password' => 'required|string|min:6',
        // Files
        'cnic_front_image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        'idcard_of_highcourt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        'license_ofhighcourt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        'passport_image' => 'required|file|mimes:jpg,jpeg,png|max:2048',
    ], [
        // Basic Info Messages
        'proposer_name.required' => 'Proposer name is required.',
        'seconder_name.required' => 'Seconder name is required.',
        'name.required' => 'Please enter your full name.',
        'guardian_name.required' => 'Please enter your father’s name.',
        'date_of_birth.required' => 'Date of birth is required.',
        'date_of_birth.date' => 'Please enter a valid date of birth.',
        'gender.required' => 'Please select your gender.',
        'gender.in' => 'Gender must be male, female, or other.',
        'caste.required' => 'Caste is required.',

        // Identification Messages
        'cnic.required' => 'CNIC is required.',
        'cnic.unique' => 'This CNIC is already registered.',
        'cnic.digits' => 'CNIC must be exactly 13 digits.',
        'bar_license_number.required' => 'Bar license number is required.',
        'bar_license_number.unique' => 'This bar license number is already registered.',

        // Address Messages
        'present_address.required' => 'Present address is required.',
        // Enrollment Messages
        'date_of_enrollment_as_advocate.required' => 'Date of enrollment as advocate is required.',
        'date_of_enrollment_as_advocate.date' => 'Please provide a valid enrollment date.',
        'date_of_enrollment_as_advocate_high_court.date' => 'High court enrollment date must be valid.',
        'district_bar_member.required' => 'District bar association is required.',
        'other_bar_member.required' => 'Other bar association information is required.',
        // Contact Messages
        'email.required' => 'Email address is required.',
        'email.email' => 'Please provide a valid email address.',
        'email.unique' => 'This email is already registered.',
        'phone.required' => 'Phone number is required.',
        'phone.unique' => 'This phone number is already registered.',
        // Auth Messages
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 6 characters long.',
        // File Messages
        'cnic_front_image.required' => 'Please upload the front image of your CNIC.',
        'cnic_front_image.mimes' => 'CNIC front image must be jpg, jpeg, png, or pdf.',
        'cnic_front_image.max' => 'CNIC front image must not exceed 2MB.',
        'idcard_of_highcourt.mimes' => 'High court ID card must be jpg, jpeg, png, or pdf.',
        'license_ofhighcourt.mimes' => 'High court license must be jpg, jpeg, png, or pdf.',
        'passport_image.mimes' => 'Passport image must be jpg, jpeg, or png.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors()
        ], 422);
    }

        // Create user directory using CNIC
        $cnicSlug = Str::slug($request->name.'_'.time());
        $path = public_path("users/files/{$cnicSlug}");

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

       
        // Upload CNIC Front
        $cnicFrontName = 'cnic_front_' . time() . '.' . $request->cnic_front_image->getClientOriginalExtension();
        $request->cnic_front_image->move($path, $cnicFrontName);
        $cnicFrontPath = "users/files/{$cnicSlug}/{$cnicFrontName}";

        // Optional files
        $idCardPath = null;
        if ($request->hasFile('idcard_of_highcourt')) {
            $name = 'highcourt_id_' . time() . '.' . $request->idcard_of_highcourt->getClientOriginalExtension();
            $request->idcard_of_highcourt->move($path, $name);
            $idCardPath = "users/files/{$cnicSlug}/{$name}";
        }

        $licensePath = null;
        if ($request->hasFile('license_ofhighcourt')) {
            $name = 'highcourt_license_' . time() . '.' . $request->license_ofhighcourt->getClientOriginalExtension();
            $request->license_ofhighcourt->move($path, $name);
            $licensePath = "users/files/{$cnicSlug}/{$name}";
        }

        $passportPath = null;
        if ($request->hasFile('passport_image')) {
            $name = 'passport_' . time() . '.' . $request->passport_image->getClientOriginalExtension();
            $request->passport_image->move($path, $name);
            $passportPath = "users/files/{$cnicSlug}/{$name}";
        }
        $scbaData = ValidAdvocate::where('reg_no', $request->bar_license_number)->first();
        $scba = false;
        if ($scbaData) {
            $scba = true;
        }
        // Create User
        $user = User::create([
            'proposer_name' => $request->proposer_name,
            'seconder_name' => $request->seconder_name,
            'name' => $request->name,
            'guardian_name' => $request->guardian_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'caste' => $request->caste,
            'cnic' => $request->cnic,
            'bar_license_number' => $request->bar_license_number,
            'cnic_front_path' => $cnicFrontPath,
            'idcard_of_highcourt_path' => $idCardPath,
            'license_ofhighcourt_path' => $licensePath,
            'passport_image' => $passportPath,
            'present_address' => $request->present_address,
            'permanent_address' => $request->present_address,
            'office_address' => $request->office_address,
            'date_of_enrollment_as_advocate' => $request->date_of_enrollment_as_advocate,
            'date_of_enrollment_as_advocate_high_court' => $request->date_of_enrollment_as_advocate_high_court,
            'district_bar_member' => $request->district_bar_member,
            'other_bar_member' => $request->other_bar_member,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' =>$request->password,
            'is_verified_hcb' => $scba,
            'is_verified_nadra' => $scba,
            'status' => 'active'
        ]);

        do {
        $cardUid = 'NFC-' . strtoupper(Str::random(10));
        } while (NfcCard::where('card_uid', $cardUid)->exists());
        NfcCard::create([
        'user_id' => $user->id,
        'card_uid' => $cardUid,
        'status' => 'active',
        'issued_at' => now(),
        ]);

        return response()->json([
        'status' => 'success',
        'message' => 'User registered successfully. NFC card assigned. Please verify email.',
        'user' => $user
        ], 201);
    }
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Please provide your email address.',
            'email.email' => 'Please provide a valid email address.',
            'email.exists' => 'This email is not registered in our system.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified) {
            return response()->json([
                'status' => 'error',
                'message' => 'This email is already verified.'
            ], 400);
        }


        $recentOtp = Otp::where('user_id', $user->id)
            ->where('type', 'register')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->whereNull('used_at')
            ->first();

        if ($recentOtp) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP already sent. Please wait 5 minutes before requesting a new one.'
            ], 429);
        }


        Otp::where('user_id', $user->id)
            ->where('type', 'register')
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $otpCode = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(5);

        Otp::create([
            'user_id'     => $user->id,
            'identifier'  => $user->email,
            'otp'         => Hash::make($otpCode),
            'type'        => 'register',
            'expires_at'  => $expiresAt,
        ]);
        Mail::to($user->email)
        ->queue(new VerifyEmail($otpCode, $user->name));
        return response()->json([
            'status'  => 'success',
            'message' => 'OTP sent successfully. Please check your email.',
        ], 200);
    }
    public function verifyotp(Request $request)
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

        if ($user->email_verified) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email already verified.'
            ], 400);
        }

        $otp = Otp::where('user_id', $user->id)
            ->where('type', 'register')
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
                'message' => 'OTP has expired.'
            ], 400);
        }

        $otp->update(['used_at' => now()]);

        $user->update([
            'email_verified'    => true,
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Email verified successfully.'
        ], 200);
    }
}
