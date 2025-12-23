<?php

namespace App\Http\Controllers\Api\Complaint;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
    $user = $request->user();
    $query = Complaint::query()
    ->with([
        'creator:id,name',
        'committee:id,name',
        'attachments',
        'remarks.user:id,name'
    ])
    ->latest();

    if ($user->role === 'admin') {

    }

    elseif ($this->isChairman($user)) {

    $committeeIds = $user->committees()
        ->wherePivot('role', 'chairman')
        ->pluck('committees.id');

    $query->whereIn('committee_id', $committeeIds);
    }

    else {
    $query->where('created_by', $user->id);
    }

    return response()->json([
    'status' => true,
    'data'   => $query->get()
    ]);
    }

public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'title'        => 'required|string|max:255',
        'description'  => 'required|string',
        'committee_id' => 'required|exists:committees,id',
        'attachments'  => 'nullable|array',
        'attachments.*'=> 'file|max:5120', // 5MB
    ]);
    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first()
        ], 422);
    }
    $user = $request->user();

    DB::beginTransaction();

    try {
        $complaint = Complaint::create([
            'title'        => $request->title,
            'description'  => $request->description,
            'committee_id' => $request->committee_id,
            'created_by'   => $user->id,
            'status'       => 'open',
        ]);

        if ($request->hasFile('attachments')) {

            $uploadPath = public_path('uploads/complaints/' . $complaint->id);

            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            foreach ($request->file('attachments') as $file) {

                $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();

                $file->move($uploadPath, $filename);

                ComplaintAttachment::create([
                    'complaint_id' => $complaint->id,
                    'uploaded_by'  => $user->id,
                    'filename'     => $file->getClientOriginalName(),
                    'file_path'    => 'uploads/complaints/' . $complaint->id . '/' . $filename,
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => 'Complaint submitted successfully',
            'data'    => $complaint->load('attachments')
        ], 201);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong',
            'error'   => $e->getMessage()
        ], 500);
    }
}
function committes(){
$committees = Committee::all(['id', 'name']);
return response()->json(['data' => $committees]);
}

private function isChairman($user)
{
    return $user->committees()
        ->wherePivot('role', 'chairman')
        ->exists();
}
}
