<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\ElectionPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ElectionPositionController extends Controller
{
    public function store(Request $request, $electionId)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'min_experience' => 'required|integer|min:1',
            'submission_price' => 'required|integer|min:1',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $election = Election::find($electionId);

        if (!$election) {
            return response()->json([
                'status' => false,
                'message' => 'Election not found'
            ], 404);
        }

        $position = ElectionPosition::create([
            'election_id' => $election->id,
            'title' => $request->title,
            'min_experience' => $request->min_experience,
            'submission_price' => $request->submission_price,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Position created successfully',
            'data' => $position
        ], 201);
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'min_experience' => 'required|integer|min:1',
            'submission_price' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $position = ElectionPosition::find($id);
        if (!$position) {
            return response()->json([
                'status' => false,
                'message' => 'Position not found'
            ], 404);
        }
        $position->update([
            'title' => $request->title,
            'min_experience' => $request->min_experience,
            'submission_price' => $request->submission_price,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Position updated successfully',
            'data' => $position
        ]);
    }
}
