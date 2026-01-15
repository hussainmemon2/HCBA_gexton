<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\ElectionApplication;
use App\Models\ElectionCandidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ElectionReviewController extends Controller
{
    public function listApplications(Election $election)
    {
    if ($election->is_complete) {
        return response()->json([
            'status' => false,
            'message' => 'Election already completed'
        ], 403);
    }

    $applications = ElectionApplication::with(['user', 'position'])
        ->where('election_id', $election->id)
        ->where('status', 'submitted')
        ->where('submission_fee_paid', true)
        ->latest()
        ->get();

    return response()->json([
        'status' => true,
        'data' => $applications
    ], 200);
    }
    public function approve(Request $request, Election $election, ElectionApplication $application)
    {
        if ($election->is_complete) {
        return response()->json([
            'status' => false,
            'message' => 'Election already completed'
        ], 403);
        }

        if (
        $application->election_id !== $election->id ||
        $application->status !== 'submitted' ||
        !$application->submission_fee_paid
        ) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid application state'
        ], 422);
        }

        $exists = ElectionCandidate::where([
        'election_id' => $election->id,
        'position_id' => $application->position_id,
        'user_id'     => $application->user_id,
        ])->exists();

        if ($exists) {
        return response()->json([
            'status' => false,
            'message' => 'Candidate already exists'
        ], 409);
        }

        DB::transaction(function () use ($election, $application) {

        ElectionCandidate::create([
            'election_id' => $election->id,
            'position_id' => $application->position_id,
            'user_id'     => $application->user_id,
        ]);

        $application->update([
            'status' => 'approved'
        ]);
        });

        return response()->json([
        'status' => true,
        'message' => 'Application approved and candidate created'
        ], 200);
    }
    public function reject(Request $request, Election $election, ElectionApplication $application)
    {
    if ($election->is_complete) {
        return response()->json([
            'status' => false,
            'message' => 'Election already completed'
        ], 403);
    }

    if (
        $application->election_id !== $election->id ||
        $application->status !== 'submitted'
    ) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid application state'
        ], 422);
    }

    $application->update([
        'status' => 'rejected'
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Application rejected'
    ], 200);
    }


}
