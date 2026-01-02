<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Models\FeeSetting;
use Illuminate\Support\Facades\Validator;

class FeesController extends Controller
{
 public function  getAnnualFee()
    {
        $feeSetting = FeeSetting::first();

        return response()->json([
            'status' => true,
            'annual_fee' => $feeSetting ? $feeSetting->annual_fee : 0
        ], 200);
    }

    public function updateAnnualFee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'annual_fee' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }
        $feeSetting = FeeSetting::first();
        if (!$feeSetting) {
            $feeSetting = new FeeSetting();
        }
        $feeSetting->annual_fee = $request->annual_fee;
        $feeSetting->save();

        return response()->json([
            'status' => true,
            'message' => 'Annual fee updated successfully.',
            'annual_fee' => $feeSetting->annual_fee
        ], 200);
    }
}
