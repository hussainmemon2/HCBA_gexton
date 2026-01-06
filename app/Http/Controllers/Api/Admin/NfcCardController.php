<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\NfcCard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NfcCardController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => NfcCard::with('user')->latest()->get()
        ]);
    }

    public function cardsByUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $user->nfcCards()->latest()->get()
        ]);
    }

    public function assign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($request->user_id);

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user->activeNfcCard) {
            return response()->json([
                'status' => false,
                'message' => 'User already has an active NFC card'
            ], 422);
        }
        do {
        $cardUid = 'NFC-' . strtoupper(Str::random(10));
        } while (NfcCard::where('card_uid', $cardUid)->exists());
        $card = NfcCard::create([
            'user_id' => $user->id,
            'card_uid' => $cardUid,
            'status' => 'active',
            'issued_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'NFC card assigned successfully',
            'data' => $card
        ], 201);
    }
}
