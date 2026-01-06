<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NfcCardRequest;
use Illuminate\Support\Facades\Validator;
class NfcCardRequestController extends Controller
{
    public function myRequests(Request $request)
    {
        $requests = $request->user()->nfcCardRequests()->latest()->get();

        return response()->json([
            'status' => true,
            'data' => $requests
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_type' => 'required|in:lost,damaged',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (! $user->activeNfcCard) {
            return response()->json([
                'status' => false,
                'message' => 'You have no active NFC card'
            ], 422);
        }

        if ($user->nfcCardRequests()->where('status', 'pending')->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'You already have a pending request'
            ], 422);
        }

        $nfcRequest = NfcCardRequest::create([
            'user_id' => $user->id,
            'request_type' => $request->request_type,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Replacement request submitted',
            'data' => $nfcRequest
        ], 201);
    }
}
