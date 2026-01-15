<?php

namespace App\Http\Controllers\Api\Auditorium;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditoriumRequest;
use App\Models\Auditorium;
use Illuminate\Http\Request;

class AuditoriumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Auditorium::query();
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('price', 'like', "%{$search}%");
        }
        $auditorium = $query->paginate(10)->through(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'price' => $item->price
            ];
        });

        return response()->json($auditorium);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AuditoriumRequest $request)
    {
        $validated = $request->validated();
        $auditorium = Auditorium::create($validated);
        return response()->json([
            'message' => 'Auditorium Created Successfully.',
            'data' => [
                'latestAuditorium' => $auditorium,
            ],
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function edit($id)
    {
        $auditorium = Auditorium::findOrFail($id);

        return response()->json([
            'message' => 'Auditorium record retrieved successfully.',
            'data' => $auditorium,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AuditoriumRequest $request, $id)
    {
        $validated = $request->validated();
        $auditorium = Auditorium::find($id);
        if (!$auditorium) {
            return response()->json([
                'message' => 'Auditorium not Found.',
            ], 404);
        }
        $auditorium->update($validated);
        return response()->json([
            'message' => 'Auditorium updated successfully.',
            'data' => $auditorium->refresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $auditorium = Auditorium::find($id);
        if (!$auditorium) {
            return response()->json([
                'message' => 'Auditorium not Found.',
            ], 404);
        }
        $auditorium->delete();
        return response()->json([
            'message' => 'Requested Auditorium removed successfully.',
            'data' => $auditorium->refresh(),
        ], 200);
    }
}
