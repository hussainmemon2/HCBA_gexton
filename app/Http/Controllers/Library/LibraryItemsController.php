<?php

namespace App\Http\Controllers\Library;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LibraryItemRequest;
use App\Models\LibraryItem;
use App\Models\LibraryItemAttachment;
use Illuminate\Http\Request;

class LibraryItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LibraryItem::query();
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%");
        }
        $library = $query->paginate(10)->through(function ($item) {
            return [
                'id' => $item->id,
                'latest_borrow' => $item->latest_borrow_record,
                'title' => $item->title,
                'files' => $item->files,
                // 'filename'=>$item->
                'type' => $item->type,
                'author_name' => $item->author_name,
                'created_at' => $item->created_at_human,
            ];
        });

        return response()->json($library);
    }

    /**
     * Edit a newly created resource in storage.
     */
    public function edit($id)
    {
        $item = LibraryItem::findOrFail($id);

        return response()->json([
            'message' => 'Library item edit request successful.',
            'data' => $item,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LibraryItemRequest $request)
    {
        $validated = $request->validated();
        $item = LibraryItem::create($validated);
        if ($request->hasFile('files')) {
            $mergeFileName = collect($request->file('files') ?? [])
                ->map(fn ($file) => FileHelper::uploadToPublic($file, 'assets/libraryAttachments'))
                ->filter()
                ->values()
                ->toArray();
            foreach ($mergeFileName as $filename) {
                LibraryItemAttachment::create([
                    'library_item_id' => $item->id,
                    'filename' => $filename]);
            }
        }

        return response()->json([
            'message' => 'Library item created successfully.',
            'data' => $item,
        ], 201);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LibraryItemRequest $request)
    {
        $item = LibraryItem::findOrFail($request->input('id'));

        $item->update($request->except('id')); // Exclude id from update

        return response()->json([
            'message' => 'Library item updated successfully.',
            'data' => $item->refresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = LibraryItem::findOrFail($id);
        $item->delete();
        $item->files()->delete();
        $item->borrowings()->delete();

        return response()->json([
            'message' => 'Library item deleted successfully.',
            'data' => $item->refresh(),
        ], 200);
    }
}
