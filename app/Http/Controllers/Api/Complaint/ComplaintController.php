<?php

namespace App\Http\Controllers\Api\Complaint;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\ComplaintHistory;
use App\Models\ComplaintTransfer;
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
                'can_transfer' => $isChairman || $user->role == 'admin',
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
                $this->ensureDirectory($uploadPath);
                foreach ($request->file('attachments') as $file) {

                    $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();

                    $file->move($uploadPath, $filename);

                    ComplaintAttachment::create([
                        'complaint_id' => $complaint->id,
                        'uploaded_by'  => $user->id,
                        'filename'     => $file->getClientOriginalName(),
                        'file_path'    => 'uploads/complaints/' . $complaint->id . '/' . $filename,
                        "status"       => "opened",
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
        $validator = Validator::make($request->all(), [
            'reason'     => 'required|string|min:5',
            'attachment' => 'nullable|file|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first()
            ], 422);
        }

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
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time().'_close.'.$file->getClientOriginalExtension();
            $dir = public_path('uploads/complaints/history');
            $this->ensureDirectory($dir);
            $file->move($dir, $filename);
            $attachmentPath = 'uploads/complaints/history/'.$filename;
        }
        $this->logHistory(
        $complaint,
        'closed',
        $request,
        $attachmentPath
        );
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

        if (! $isChairmanOfCommittee && $user->role != 'admin') {
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
            'remarks.user:id,name',
            'transfers.fromCommittee:id,name',
            'transfers.toCommittee:id,name',
            'transfers.user:id,name',
            'histories.user:id,name',
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
        $canTransfer = $isChairman || $user->role === 'admin';

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
                    'can_add_remark' => $canAddRemark,
                    'can_close'      => $canClose,
                    'can_transfer'   => $canTransfer,
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
            'reason'    => 'required_if:satisfied,false|string',
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
            $this->logHistory(
            $complaint,
            'reopened',
            $request,
            null
            );
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
    public function transfer(Request $request, $id)
    {
        $user = $request->user();

        $validate = Validator::make($request->all(), [
            'to_committee_id' => 'required|exists:committees,id',
            'reason'          => 'required|string|min:5',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validate->errors()->first()
            ], 422);
        }

        $complaint = Complaint::findOrFail($id);

        $isChairman = $user->committees()
            ->wherePivot('role', 'chairman')
            ->where('committee_id', $complaint->committee_id)
            ->exists();

        if (! $isChairman && $user->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admin or committee chairman can transfer complaint'
            ], 403);
        }

        if ($complaint->committee_id == $request->to_committee_id) {
            return response()->json([
                'status' => false,
                'message' => 'Complaint is already in this committee'
            ], 422);
        }

        DB::beginTransaction();

        try {
            ComplaintTransfer::create([
                'complaint_id'      => $complaint->id,
                'from_committee_id' => $complaint->committee_id,
                'to_committee_id'   => $request->to_committee_id,
                'transferred_by'    => $user->id,
                'reason'            => $request->reason,
            ]);

            $complaint->update([
                'committee_id' => $request->to_committee_id,
                'status'       => 'open',
                'closed_at'    => null,
            ]);

            $complaint->remarks()->create([
                'user_id' => $user->id,
                'role'    => $user->role == 'admin' ? 'admin' : 'chairman',
                'remark'  => 'Complaint transferred: ' . $request->reason,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Complaint transferred successfully'
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
    private function logHistory(Complaint $complaint,string $toStatus,Request $request,?string $attachmentPath = null)
    {
    ComplaintHistory::create([
        'complaint_id' => $complaint->id,
        'from_status'  => $complaint->status,
        'to_status'    => $toStatus,
        'changed_by'   => $request->user()->id,
        'reason'       => $request->reason ?? null,
        'attachment'   => $attachmentPath,
    ]);
    }
    public function reject(Request $request, $id)
    {
        $user = $request->user();

        $validate = Validator::make($request->all(), [
            'reason'     => 'required|string|min:5',
            'attachment' => 'nullable|file|max:5120',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validate->errors()->first()
            ], 422);
        }

        $complaint = Complaint::findOrFail($id);

        $isChairman = $user->committees()
            ->wherePivot('role', 'chairman')
            ->where('committee_id', $complaint->committee_id)
            ->exists();

        if (! $isChairman && $user->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admin or committee chairman can reject complaint'
            ], 403);
        }

        if ($complaint->status === 'rejected') {
            return response()->json([
                'status' => false,
                'message' => 'Complaint already rejected'
            ], 422);
        }
        if ($complaint->status === 'closed') {
            return response()->json([
                'status' => false,
                'message' => 'Complaint already closed'
            ], 422);
        }
        DB::beginTransaction();

        try {
            $attachmentPath = null;

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = time().'_reject.'.$file->getClientOriginalExtension();
                $dir = public_path('uploads/complaints/history');
                $this->ensureDirectory($dir);
                $file->move($dir, $filename);
                $attachmentPath = 'uploads/complaints/history/'.$filename;
            }

            ComplaintHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $complaint->status,
                'to_status'    => 'rejected',
                'changed_by'   => $user->id,
                'reason'       => $request->reason,
                'attachment'   => $attachmentPath,
            ]);

            $complaint->update([
                'status'    => 'rejected',
                'closed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Complaint rejected successfully'
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
    private function ensureDirectory($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }

}
