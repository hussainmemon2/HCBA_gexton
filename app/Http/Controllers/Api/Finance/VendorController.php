<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    public function index(){
        $vendors = Vendor::orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'total' => $vendors->count(),
            'vendors' => $vendors
        ]);
    }
    public function store(Request $request){
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:vendors',
            'phone' => 'nullable|string|max:20',
            'product' => 'nullable|string|max:255',
            'description' => 'nullable|string',

        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()
            ], 422);
        }

        $vendor = Vendor::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Vendor created successfully',
            'vendor' => $vendor
        ]);

    }

    function show($id){
    $vendor = Vendor::find($id);
    if(!$vendor){
        return response()->json([
            'status' => false,
            'message' => 'Vendor not found'
        ], 404);
    }
    return response()->json([
        'status' => true,
        'vendor' => $vendor
    ]);
    }

    function update(Request $request, $id){
        $vendor = Vendor::find($id);
        if(!$vendor){
            return response()->json([
                'status' => false,
                'message' => 'Vendor not found'
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:vendors,email,' . $vendor->id,
            'phone' => 'nullable|string|max:20',
            'product' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()
            ], 422);
        }

        $vendor->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Vendor updated successfully',
            'vendor' => $vendor
        ]);
    }

}