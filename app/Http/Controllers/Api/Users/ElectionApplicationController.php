<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\ElectionApplication;
use App\Models\ElectionPosition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
class ElectionApplicationController extends Controller
{
    public function apply(Request $request, Election $election)
    {
        if (!$election->is_active || $election->is_complete) {
            return response()->json([
                'status' => false,
                'message' => 'Election is not open for applications'
            ], 403);
        }
        $exists = ElectionApplication::where([
            'election_id' => $election->id,
            'user_id'     => $request->user()->id,
        ])->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'You have already applied for this election'
            ], 409);
        }
        $application = ElectionApplication::create([
            'election_id' => $election->id,
            'user_id'     => $request->user()->id,
            'status'      => 'draft',
        ]);
        // $electionfee = $election->application_fee;
        // ElectionPayment::create([
        //     'election_id' => $election->id,
        //     'user_id'     => $request->user()->id,
        //     'type'        => 'application_fee',
        //     'amount'      => $electionfee,
        //     'status'      => 'pending'
        // ]);
        return response()->json([
            'status' => true,
            'message' => 'Application started successfully',
            'data' => $application
        ], 201);
    }
    public function submit(Request $request, Election $election)
    {
        if ($error = $this->ensureElectionIsActive($election)) {
            return $error;
        }

        $validator = Validator::make($request->all(), [
            'position_id' => 'required|exists:election_positions,id',
            'vakalatnama' => 'required|file|mimes:pdf,jpg,png',
            'case_order'  => 'required|file|mimes:pdf,jpg,png',
            'fee_challan_of_bar_card' => 'required|file|mimes:pdf,jpg,png',
            'bar_certificate' => 'required|file|mimes:pdf,jpg,png',
            'no_dues_cert_from_high_court' => 'required|file|mimes:pdf,jpg,png',
            'no_dues_cert_from_sindh_bar' => 'required|file|mimes:pdf,jpg,png',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $user = $request->user();
        if (!$user->date_of_enrollment_as_advocate_high_court) {
            return response()->json([
                'status' => false,
                'message' => 'Enrollment date not found'
            ], 403);
        }

        $experienceYears = Carbon::parse(
            $user->date_of_enrollment_as_advocate_high_court
        )->diffInYears(now());
  
    //  * Check position eligibility
    
        $position = ElectionPosition::where([
            'id' => $request->position_id,
            'election_id' => $election->id,
        ])->first();

        if (!$position) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid position for this election'
            ], 404);
        }

        if ($experienceYears < $position->min_experience) {
            return response()->json([
                'status' => false,
                'message' => "Minimum {$position->min_experience} years experience required for this position"
            ], 403);
        }
        
        $application = ElectionApplication::where([
            'election_id' => $election->id,
            'user_id'     => $request->user()->id,
        ])->first();

        if (!$application || !$application->application_fee_paid) {
            return response()->json([
                'status' => false,
                'message' => 'Application fee must be paid first'
            ], 403);
        }

        if ($application->status !== 'draft') {
            return response()->json([
                'status' => false,
                'message' => 'Application already submitted'
            ], 409);
        }

        $safeElectionName = Str::slug($election->name); // Bar Election 2026 -> bar-election-2026

        $basePath = public_path(
            "uploads/elections/{$safeElectionName}/applications/{$application->id}"
        );

        if (!File::exists($basePath)) {
            File::makeDirectory($basePath, 0755, true);
        }

        $paths = [];

        foreach ($request->allFiles() as $field => $file) {
            $filename = $field . '.' . $file->getClientOriginalExtension();

            $file->move($basePath, $filename);

            // Store relative path (public access)
            $paths[$field] = "uploads/elections/{$safeElectionName}/applications/{$application->id}/{$filename}";
        }

        $application->update(array_merge($paths, [
            'position_id'=>$request->position_id,
            'status' => 'submitted'
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Application submitted successfully'
        ], 200);
    }
    private function ensureElectionIsActive(Election $election)
    {
        if (!$election->is_active) {
            return response()->json([
                'status' => false,
                'message' => 'Election is not active'
            ], 403);
        }

        if ($election->is_complete) {
            return response()->json([
                'status' => false,
                'message' => 'Election is already completed'
            ], 403);
        }

        return null;
    }
}
