<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sticker;
use Illuminate\Http\Request;

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
}
