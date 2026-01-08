<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\ElectionApplication;
use App\Models\ElectionCandidate;
use App\Models\ElectionPayment;
use Illuminate\Http\Request;

class ElectionController extends Controller
{
public function index(Request $request)
{
    $election = Election::where('is_active', true)
        ->with('positions')
        ->first();

    if (!$election) {
        return response()->json([
            'status' => false,
            'is_active' => false,
            'message' => 'No active election'
        ], 404);
    }

    if ($request->user()->annual_fee_paid != 1) {
        return response()->json([
            'status' => false,
            'is_active' => true,
            'message' => 'User is not eligible to participate in the active election'
        ], 403);
    }
    $application = ElectionApplication::where([
        'election_id' => $election->id,
        'user_id'     => $request->user()->id
    ])->first();

    $candidate = null;
    if ($application && $application->status === 'approved') {
        $candidate = ElectionCandidate::where([
            'election_id' => $election->id,
            'user_id'     => $request->user()->id,
            'position_id'=> $application->position_id
        ])->first();
    }

    return response()->json([
        'status' => true,
        'is_active' => true,
        'data' => [
            'id' => $election->id,
            'name' => $election->name,
            'application_fee' => $election->application_fee,
            'submission_fee' => $election->submission_fee,
            'start_date' => $election->start_date,
            'end_date' => $election->end_date,
            'positions' => $election->positions,
            'has_applied' => (bool) $application,
            'application' => $application ? [
                'id' => $application->id,
                'position_id' => $application->position_id,
                'status' => $application->status,
                'application_fee_paid' => (bool) $application->application_fee_paid,
                'submission_fee_paid' => (bool) $application->submission_fee_paid,
            ] : null,

            'is_candidate' => (bool) $candidate,
        ]
    ], 200);
}


}
