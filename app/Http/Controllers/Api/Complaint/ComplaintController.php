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

        $isChairman = $this->isChairman($user);

        $query = Complaint::query()
        ->with([
            'creator:id,name',
            'committee:id,name',
            'attachments',
            'remarks.user:id,name'
        ])
        ->latest();
        if ($user->role == 'admin') {
        // Admin can see all complaints
        }
        elseif ($isChairman) {

        $committeeIds = $user->committees()
            ->wherePivot('role', 'chairman')
            ->pluck('committees.id');

        $query->whereIn('committee_id', $committeeIds);
        }
        else {
        $query->where('created_by', $user->id);
        }

        $complaints = $query->get();

        return response()->json([
        'status'      => true,
        'is_chairman' => $isChairman,
        'is_admin'    => $user->role == 'admin',
        'data'        => $complaints->map(function ($complaint) use ($user, $isChairman) {

            return [
                'id'          => $complaint->id,
                'title'       => $complaint->title,
                'description' => $complaint->description,
                'status'      => $complaint->status,
                'committee'   => $complaint->committee,
                'creator'     => $complaint->creator,
                'attachments' => $complaint->attachments,

                'can_add_remark' => $isChairman &&
                    $user->committees()
                        ->wherePivot('role', 'chairman')
                        ->where('committee_id', $complaint->committee_id)
                        ->exists() || $user->role == 'admin',

                'can_close' => $isChairman &&
                    $user->committees()
                        ->wherePivot('role', 'chairman')
                        ->where('committee_id', $complaint->committee_id)
                        ->exists() || $user->role == 'admin',
            ];
        }),
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
    public function close(Request $request, $complaintId)
    {
        $user = $request->user();

        $complaint = Complaint::findOrFail($complaintId);

        $isChairmanOfCommittee = $user->committees()
            ->wherePivot('role', 'chairman')
            ->where('committee_id', $complaint->committee_id)
            ->exists();

        if (! $isChairmanOfCommittee) {
            return response()->json([
                'status' => false,
                'message' => 'Only committee chairman can close this complaint'
            ], 403);
        }

        if ($complaint->status === 'closed') {
            return response()->json([
                'status' => false,
                'message' => 'Complaint is already closed'
            ], 422);
        }

        $complaint->update([
            'status'    => 'closed',
            'closed_at'=> now(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Complaint closed successfully'
        ]);
    }
    public function addRemark(Request $request, $complaintId)
    {
         $user = $request->user();

       $validate = Validator::make($request->all(), [
            'remark' => 'required|string'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validate->errors()->first()
            ], 422);
        }
        $complaint = Complaint::findOrFail($complaintId);

        $isChairmanOfCommittee = $user->committees()
            ->wherePivot('role', 'chairman')
            ->where('committee_id', $complaint->committee_id)
            ->exists();

        if (! $isChairmanOfCommittee && !$user->role == 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only committee chairman and admin can add remarks'
            ], 403);
        }
        if ($complaint->status === 'closed' && $complaint->user_satisfied === true) {
            return response()->json([
            'status' => false,
            'message' => 'Complaint is permanently closed'
            ], 422);
        }

        $complaint->remarks()->create([
            'user_id' => $user->id,
            'role'    => $user->role == 'admin' ? 'admin' : 'chairman',
            'remark'  => $request->remark,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Remark added successfully'
        ], 201);
    }
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $complaint = Complaint::with([
            'creator:id,name',
            'committee:id,name',
            'attachments',
            'remarks.user:id,name'
        ])->findOrFail($id);

        $isChairman = $user->committees()
            ->wherePivot('role', 'chairman')
            ->where('committee_id', $complaint->committee_id)
            ->exists();

        $isOwner = $complaint->created_by === $user->id;

        if (! $isChairman && ! $isOwner && $user->role != 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $canAddRemark = $isChairman || $user->role == 'admin';
        $canClose     = $isChairman || $user->role == 'admin';

        $askUser = false;
        if (
            $isOwner &&
            $complaint->status === 'closed' &&
            is_null($complaint->user_satisfied)
        ) {
            $askUser = true;
        }

        return response()->json([
            'status' => true,
            'data' => [
                'complaint' => $complaint,
                'flags' => [
                    'is_chairman'    => $isChairman,
                    'is_owner'       => $isOwner,
                    'can_add_remark' => $canAddRemark ,
                    'can_close'      => $canClose,
                    'ask_user'       => $askUser,
                ]
            ]
        ]);
    }
    public function respondSatisfaction(Request $request, $id)
    {
        $user = $request->user();

        $validate = Validator::make($request->all(), [
            'satisfied' => 'required|boolean',
            'remark'    => 'required_if:satisfied,false|string',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validate->errors()->first()
            ], 422);
        }
        $complaint = Complaint::findOrFail($id);

        if ($complaint->created_by !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if (
            $complaint->status !== 'closed' ||
            ! is_null($complaint->user_satisfied)
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid complaint state'
            ], 422);
        }

        DB::beginTransaction();

        try {

            if ($request->satisfied == true) {

                $complaint->update([
                    'user_satisfied' => true
                ]);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Complaint closed permanently'
                ]);
            }

            $complaint->update([
                'status'         => 'reopened',
                'user_satisfied' => null,
                'closed_at'      => null,
            ]);

            $complaint->remarks()->create([
                'user_id' => $user->id,
                'role'    => 'member',
                'remark'  => $request->remark,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Complaint reopened with remark'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
