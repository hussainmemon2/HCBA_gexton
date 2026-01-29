<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ElectionController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Election::with('positions')->orderBy('created_at', 'desc');

        if ($request->status === 'active') {
            $query->where('is_active', true);
        }

        if ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $elections = $query->get();

        return response()->json([
            'status' => true,
            'count' => $elections->count(),
            'data' => $elections
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'application_fee' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        Election::where('is_active', true)->update(['is_active' => false]);

        $election = Election::create([
            'name' => $request->name,
            'application_fee' => $request->application_fee,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => true,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Election enabled successfully',
            'data' => $election
        ], 201);
    }
    public function disable($id)
    {
        $election = Election::find($id);

        if (!$election) {
            return response()->json([
                'status' => false,
                'message' => 'Election not found'
            ], 404);
        }

        $election->update(['is_active' => false]);

        return response()->json([
            'status' => true,
            'message' => 'Election disabled successfully'
        ]);
    }
    public function enable($id)
    {
        $election = Election::find($id);

        if (!$election) {
            return response()->json([
                'status' => false,
                'message' => 'Election not found'
            ], 404);
        }
        Election::where('is_active', true)->update(['is_active' => false]);
        $election->update(['is_active' => true]);
        return response()->json([
            'status' => true,
            'message' => 'Election enabled successfully'
        ]);
    }
}
