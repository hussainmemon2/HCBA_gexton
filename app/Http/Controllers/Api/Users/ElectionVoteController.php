<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\ElectionCandidate;
use Illuminate\Http\Request;

class ElectionVoteController extends Controller
{
    public function listCandidates(Election $election)
    {
    if (!$election->is_active || $election->is_complete) {
        return response()->json([
            'status' => false,
            'message' => 'Election is not open for voting'
        ], 403);
    }

    $candidates = ElectionCandidate::with(['user:id,name', 'position:id,title'])
        ->where('election_id', $election->id)
        ->get()
        ->groupBy('position.title');

    return response()->json([
        'status' => true,
        'data' => $candidates
    ], 200);
    }
}
