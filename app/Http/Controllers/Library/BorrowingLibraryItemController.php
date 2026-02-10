<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowLibraryItemRequest;
use App\Models\Borrowing;
use App\Models\LibraryItem;
use App\Models\User;

class BorrowingLibraryItemController extends Controller
{
    /**
     * Display a listing of borrowing records (with optional search).
     */
    public function fetchBorrowHistory($bookID)
    {
        $libraryItem = LibraryItem::find($bookID);

        if (! $libraryItem) {
            return response()->json([
                'message' => 'Library item not found.',
            ], 404);
        }
        $borrow = Borrowing::where('library_item_id', $bookID)->get();

        return response()->json([
            'message' => 'Borrowing record retrieved successfully.',
            'data' => [
                'borrow_history' => $borrow,
            ],
        ], 200);
    }

    /**
     * Show details of a specific borrowing record (for editing/viewing).
     */
    public function edit($id)
    {
        $borrow = Borrowing::with(['user', 'libraryItem'])->findOrFail($id);

        return response()->json([
            'message' => 'Borrowing record retrieved successfully.',
            'data' => $borrow,
        ], 200);
    }

    /**
     * Store a new borrowing record (borrow an item).
     */
    public function store(BorrowLibraryItemRequest $request)
    {

        $validated = $request->validated();

        // Auto set user_id for returning if not provided
        if ($validated['status'] === 'returned') {
            $latestBorrowing = Borrowing::where('library_item_id', $validated['library_item_id'])
                ->where('status', 'borrowed')
                ->latest('id')
                ->first();

            if ($latestBorrowing) {
                $validated['user_id'] = $latestBorrowing->user_id;
            }
            $validated['date'] = date('Y-m-d');
        } elseif ($validated['status'] === 'borrowed') {
            $user = User::where('cnic', $validated['cnic_number'])->first();
            $validated['user_id'] = $user->id;
        } elseif ($validated['status'] === 'reserved') {
            $validated['user_id'] = $request->user()->id;
            $validated['date'] = date('Y-m-d');
        }

        $borrow = Borrowing::create($validated);

        $message = $validated['status'] === 'reserved' ? 'Item has been successfully reserved.' : 'Item has been successfully borrowed.';

        return response()->json([
            'message' => $message,
            'data' => $borrow->load(['libraryItem']),
        ], 201);
    }


}
