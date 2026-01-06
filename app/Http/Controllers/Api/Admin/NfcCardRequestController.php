<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\NfcCard;
use App\Models\NfcCardRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NfcCardRequestController extends Controller
{


    public function index()
    {
        $requests = NfcCardRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $requests
        ]);
    }

    public function approve($id,Request $request )
    {
        $requestModel = NfcCardRequest::find($id);
        if(!$requestModel){
            return response()->json([
                'status' => false,
                'message' => 'Request not found'
            ], 404);
        }
        if ($requestModel->status != 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Request already processed'
            ], 422);
        }
        do{
        $cardUid = 'NFC-' . strtoupper(Str::random(10));
        } while (NfcCard::where('card_uid', $cardUid)->exists());

     

        DB::transaction(function () use ($request, $requestModel , $cardUid) {
              NfcCard::where('user_id', $requestModel->user_id)
                ->where('status', 'active')
                ->update([
                    'status' => 'blocked',
                    'blocked_at' => now(),
                ]);

            NfcCard::create([
                'user_id' => $requestModel->user_id,
                'card_uid' => $cardUid,
                'status' => 'active',
                'issued_at' => now(),
            ]);

            $requestModel->update([
                'status' => 'approved',
                'processed_by' => $request->user()->id,
                'processed_at' => now(),
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'Request approved and new NFC card assigned'
        ]);
    }
    public function reject(Request $request , $id)
    {
        $requestModel = NfcCardRequest::find($id);
        if(!$requestModel){
            return response()->json([
                'status' => false,
                'message' => 'Request not found'
            ], 404);
        }
        if ($requestModel->status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Request already processed'
            ], 422);
        }

        $requestModel->update([
            'status' => 'rejected',
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Request rejected'
        ]);
    }
}