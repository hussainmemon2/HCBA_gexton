<?php

namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use App\Models\Sticker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
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
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'price'       => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $uploadPath = public_path('uploads/stickers');

            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            $imageName = time().'_'.$request->image->getClientOriginalName();
            $request->image->move($uploadPath, $imageName);

            $imagePath = 'uploads/stickers/'.$imageName;
        }

        $sticker = Sticker::create([
            'name'        => $request->name,
            'description' => $request->description,
            'image'       => $imagePath,
            'price'       => $request->price,
        ]);

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
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'price'       => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('image')) {
            // Delete old image
            if ($sticker->image && File::exists(public_path($sticker->image))) {
                File::delete(public_path($sticker->image));
            }

            $uploadPath = public_path('uploads/stickers');

            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            $imageName = time().'_'.$request->image->getClientOriginalName();
            $request->image->move($uploadPath, $imageName);

            $sticker->image = 'uploads/stickers/'.$imageName;
        }

        $sticker->update($request->only([
            'name',
            'description',
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

        if ($sticker->image && File::exists(public_path($sticker->image))) {
            File::delete(public_path($sticker->image));
        }

        $sticker->delete();

        return response()->json([
            'status' => true,
            'message' => 'Sticker deleted successfully'
        ]);
    }

}
