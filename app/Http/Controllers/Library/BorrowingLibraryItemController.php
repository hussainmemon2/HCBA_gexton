<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowLibraryItemRequest;
use App\Models\Borrowing;
use App\Models\LibraryItem;

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
        if ($validated['status'] === 'returned' && empty($validated['user_id'])) {
            $latestBorrowing = Borrowing::where('library_item_id', $validated['library_item_id'])
                ->where('status', 'borrowed')
                ->latest('id')
                ->first();

            if ($latestBorrowing) {
                $validated['user_id'] = $latestBorrowing->user_id;
            }
        }

        $validated['date'] = now()->toDateString();
        // $validated['status'] = 'borrowed';

        $borrow = Borrowing::create($validated);

        // Optionally update library item status to checked_out
        // LibraryItem::where('id', $borrow->library_item_id)
        //     ->update(['status' => 'borrowed']);

        return response()->json([
            'message' => 'Item has been successfully borrowed.',
            'data' => $borrow->load(['libraryItem']),
        ], 201);
    }

    /**
     * Update a borrowing record (e.g., mark as returned or extend).
     */
    public function update(BorrowLibraryItemRequest $request)
    {
        $borrow = Borrowing::findOrFail($request->input('id'));

        $borrow->update($request->except('id'));

        // If status changed to 'returned', update library item status back to available
        if ($borrow->fresh()->status === 'returned') {
            LibraryItem::where('id', $borrow->library_item_id)
                ->update(['status' => 'available']);
        }

        return response()->json([
            'message' => 'Borrowing record updated successfully.',
            'data' => $borrow->refresh()->load(['libraryItem']),
        ], 200);
    }

    /**
     * Delete/cancel a borrowing record (admin only).
     */
    // public function destroy($id)
    // {
    //     $borrow = Borrowing::findOrFail($id);

    //     // Revert item status if currently borrowed
    //     if ($borrow->status === 'borrowed') {
    //         LibraryItem::where('id', $borrow->library_item_id)
    //             ->update(['status' => 'available']);
    //     }

    //     $borrow->delete();

    //     return response()->json([
    //         'message' => 'Borrowing record deleted successfully.',
    //     ], 200);
    // }
}
