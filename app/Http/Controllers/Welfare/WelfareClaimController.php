<?php

namespace App\Http\Controllers\Welfare;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\WelfareRequest;
use App\Models\User;
use App\Models\WelfareClaim;
use App\Models\WelfareClaimAttachment;
use App\Models\WelfareClaimRemark;
use Illuminate\Http\Request;

class WelfareClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $loggedInUser = $request->user();
        $loggedInUserRole = $loggedInUser->role;

        $query = WelfareClaim::with(['claimer', 'user', 'attachments', 'remarks'])
            ->when($loggedInUserRole === 'member', function ($q) use ($loggedInUser) {
                $q->where('user_id', $loggedInUser->id);
            });

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('type', 'like', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('claimer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        $welfare = $query->paginate(10)->through(function ($item) {
            return [
                'id' => $item->id,
                'claimer' => $item->claimer->name,
                'user' => $item->user->name,
                'attachments' => $item->attachments->pluck('filename'),
                'remarks' => $item->remarks->pluck('remark'),
                'type' => $item->type,
                'amount' => $item->amount,
                'reason' => $item->reason,
                'received_date' => $item->received_date,
                'approved_date' => $item->approved_date,
                'funding_date' => $item->funding_date,
                'ready_date' => $item->ready_date,
                'rejected_date' => $item->rejected_date,
                'collected_date' => $item->collected_date,

                'status' => $item->status,
                'created_at' => $item->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json($welfare);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WelfareRequest $request)
    {
        $loggedInUserId = $request->user()->id;
        // dd($loggedInUserId = $request->user()->role);

        // claimer_id will be nullable claimer can claim for another user
        // when claiming for another user claimer id is required when claiming for himself than not required
        // cnic for existence check
        // claimer_id when requesting for another user
        // user will give description required
        // admin can add multiple remarks

        $validated = $request->validated();
        if (empty($validated['claimer_id'])) {
            $validated['claimer_id'] = $loggedInUserId;
        }

        $user = User::where('cnic', $validated['cnic_number'])->firstOrFail();
        unset($validated['cnic_number']);
        $validated['user_id'] = $user->id;

        // Set received_date if status is received
        $validated['status'] = 'received';
        // {
        $validated['received_date'] = now()->toDateString();        // }

        $claim = WelfareClaim::create($validated);

        if ($request->hasFile('files')) {
            $uploadedFiles = collect($request->file('files'))
                ->map(fn ($file) => FileHelper::uploadToPublic($file, 'assets/welfareAttachments'))
                ->filter()
                ->values()
                ->toArray();

            foreach ($uploadedFiles as $filename) {
                WelfareClaimAttachment::create([
                    'welfare_claim_id' => $claim->id,
                    'filename' => $filename,
                ]);
            }
        }

        return response()->json([
            'message' => 'Welfare claim created successfully.',
            'data' => $claim->load(['claimer', 'user', 'attachments', 'remarks']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $claim = WelfareClaim::with(['claimer', 'user', 'attachments', 'remarks'])->findOrFail($id);

        return response()->json([
            'message' => 'Welfare claim retrieved successfully.',
            'data' => $claim,
        ], 200);
    }
    /**
     * Update the specified resource in storage.
     */
    // public function update(WelfareRequest $request)
    // {
    //     $claim = WelfareClaim::findOrFail($request->input('id'));

    //     $validated = $request->except(['id', 'files']);
    //     $claim->update($validated);

    //     // If files are provided, upload them
    //     if ($request->hasFile('files')) {
    //         $uploadedFiles = collect($request->file('files'))
    //             ->map(fn($file) => FileHelper::uploadToPublic($file, 'assets/welfareAttachments'))
    //             ->filter()
    //             ->values()
    //             ->toArray();

    //         foreach ($uploadedFiles as $filename) {
    //             WelfareClaimAttachment::create([
    //                 'welfare_claim_id' => $claim->id,
    //                 'filename' => $filename,
    //             ]);
    //         }
    //     }

    //     return response()->json([
    //         'message' => 'Welfare claim updated successfully.',
    //         'data' => $claim->refresh()->load(['claimer', 'user', 'attachments', 'remarks']),
    //     ], 200);
    // }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy($id)
    // {
    //     $claim = WelfareClaim::findOrFail($id);

    //     $claim->attachments()->delete();
    //     $claim->remarks()->delete();
    //     $claim->delete();

    //     return response()->json([
    //         'message' => 'Welfare claim deleted successfully.',
    //     ], 200);
    // }

    /**
     * Add a remark to a welfare claim.
     */
    public function addRemark(Request $request, $id)
    {
        $request->validate([
            'remark' => 'required|string',
        ]);

        $claim = WelfareClaim::findOrFail($id);

        WelfareClaimRemark::create([
            'welfare_claim_id' => $claim->id,
            'remark' => $request->input('remark'),
        ]);

        return response()->json([
            'message' => 'Remark added successfully.',
        ], 200);
    }

    /**
     * Update the status of a welfare claim.
     */
    public function updateStatus(Request $request, $id)
    {
        // if receiving amount means status is ready
        $request->validate([
            'status' => 'required|in:funding,ready,rejected,collected',
        ]);
        // if($request->status == 'rejected'){

        // }

        $claim = WelfareClaim::findOrFail($id);

        // Check if the claim is already closed
        if (in_array($claim->status, ['rejected', 'collected'])) {
            return response()->json([
                'message' => 'This claim has already been closed or rejected.',
            ], 422);
        }

        $status = $request->status;

        $updateData = ['status' => $status];

        if ($status == 'funding') {
            $updateData['funding_date'] = now()->toDateString();
        } elseif ($status == 'ready') {
            $updateData['ready_date'] = now()->toDateString();
        } elseif ($status == 'collected') {
            $updateData['collected_date'] = now()->toDateString();
        } elseif ($status == 'rejected') {
            $updateData['rejected_date'] = now()->toDateString(); // Assuming rejected also sets collected_date
        }

        $claim->update($updateData);

        // Add remark if status is rejected
        // removed updating status only
        // if ($status == 'rejected' && $request->has('remark')) {
        //     WelfareClaimRemark::create([
        //         'welfare_claim_id' => $claim->id,
        //         'remark' => $request->input('remark'),
        //     ]);
        // }

        return response()->json([
            'message' => 'Status updated successfully.',
            'data' => $claim->load(['claimer', 'user', 'attachments', 'remarks']),
        ], 200);
    }

    public function updateAmount(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required',
        ]);
        $claim = WelfareClaim::findOrFail($id);
        $claim->update($validated);

        return response()->json([
            'message' => 'Amount updated successfully.',
            'data' => $claim->load(['claimer', 'user', 'attachments', 'remarks']),
        ], 200);
    }
}
