<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\NfcCard;
use Illuminate\Http\Request;

class NfcCardController extends Controller
{
    public function myCards(Request $request)
    {
        $user = $request->user();
        $cards = $user->nfcCards()->latest()->get();
        return response()->json([
            'status' => true,
            'data' => $cards
        ]);
    }

    public function toggleCardStatus(Request $request, NfcCard $card)
    {
        $user = $request->user();

        if ($card->user_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'You do not own this card'
            ], 403);
        }

        if ($card->status === 'blocked') {
            return response()->json([
                'status' => false,
                'message' => 'This card is blocked and cannot be changed'
            ], 422);
        }

        $card->status = $card->status === 'active' ? 'inactive' : 'active';
        $card->save();

        return response()->json([
            'status' => true,
            'message' => 'Card status updated',
            'data' => $card
        ]);
    }
}
