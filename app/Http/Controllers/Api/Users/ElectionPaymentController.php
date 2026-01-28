<?php

namespace App\Http\Controllers\Api\Users;


use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\ElectionApplication;
use App\Models\ElectionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ElectionPaymentController extends Controller
{
    public function payApplicationFee(Request $request, Election $election)
    {
        if ($error = $this->ensureElectionIsActive($election)) {
            return $error;
        }
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $application = ElectionApplication::where([
            'election_id' => $election->id,
              'user_id'     => $request->user()->id,
        ])->first();

        if (!$application) {
            return response()->json([
                'status' => false,
                'message' => 'Application not found'
            ], 404);
        }

        if ($application->application_fee_paid) {
            return response()->json([
                'status' => false,
                'message' => 'Application fee already paid'
            ], 409);
        }

        ElectionPayment::create([
            'election_id' => $election->id,
            'user_id'     => $request->user()->id,
            'type'        => 'application_fee',
            'amount'      => $election->application_fee,
            'transaction_id' => $request->transaction_id,
            'payment_gateway' => 'manual',
            'status'      => 'paid'
        ]);

        $application->application_fee_paid = true;
        $application->save();

        return response()->json([
            'status' => true,
            'message' => 'Application fee paid successfully',
            'data' => $application
        ], 200);
    }

    public function paySubmissionFee(Request $request, Election $election)
    {
        if ($error = $this->ensureElectionIsActive($election)) {
            return $error;
        }

        $validator = Validator::make($request->all(), [
            // 'position_id'     => 'required|exists:election_positions,id',
            'transaction_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $application = ElectionApplication::where([
            'election_id' => $election->id,
            // 'position_id' => $request->position_id,
            'user_id'     => $request->user()->id,
        ])->first();

        if (!$application || $application->status !== 'submitted') {
            return response()->json([
                'status' => false,
                'message' => 'Application must be submitted first'
            ], 403);
        }

        if ($application->submission_fee_paid) {
            return response()->json([
                'status' => false,
                'message' => 'Submission fee already paid'
            ], 409);
        }
        
        DB::transaction(function () use ($election, $application, $request) {

            ElectionPayment::create([
                'election_id'    => $election->id,
                'user_id'        => $request->user()->id,
                'type'           => 'submission_fee',
                'amount'         => $application->position->submission_price,
                'transaction_id'=> $request->transaction_id,
                'payment_gateway'=> 'manual',
                'status'         => 'paid'
            ]);

            $application->update([
                'submission_fee_paid' => true
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'Submission fee paid successfully'
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
