<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\LibraryItem;

class PublicApisController extends Controller
{
    public function libraryItems()
    {
        $fetchLatestTenItem = LibraryItem::latest('id')->take(10)->get();

        return response()->json([
            'status' => 'received successfully',
            'data' => $fetchLatestTenItem,
        ]);

    }

    public function fetchActiveElection()
    {
        $fetchActiveElection = Election::where('is_active', true)->latest('id')->first();

        return response()->json([
            'status' => 'received successfully',
            'data' => $fetchActiveElection,
        ]);
    }
}
