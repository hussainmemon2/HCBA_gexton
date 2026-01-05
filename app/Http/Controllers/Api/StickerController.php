<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Sticker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StickerController extends Controller
{
    
    public function index()
    {
        $stickers = Sticker::latest()->get();

        return response()->json([
            'status' => true,
            'data' => $stickers
        ]);
    }

   
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $sticker = Sticker::create($request->only([
            'name',
            'description',
            'image',
            'price'
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Sticker created successfully',
            'data' => $sticker
        ], 201);
    }

    public function show($id)
    {
        $sticker = Sticker::find($id);

        if (!$sticker) {
            return response()->json([
                'status' => false,
                'message' => 'Sticker not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $sticker
        ]);
    }


    public function update(Request $request, $id)
    {
        $sticker = Sticker::find($id);

        if (!$sticker) {
            return response()->json([
                'status' => false,
                'message' => 'Sticker not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $sticker->update($request->only([
            'name',
            'description',
            'image',
            'price'
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Sticker updated successfully',
            'data' => $sticker
        ]);
    }

    public function destroy($id)
    {
        $sticker = Sticker::find($id);

        if (!$sticker) {
            return response()->json([
                'status' => false,
                'message' => 'Sticker not found'
            ], 404);
        }

        $sticker->delete();

        return response()->json([
            'status' => true,
            'message' => 'Sticker deleted successfully'
        ]);
    }
}
