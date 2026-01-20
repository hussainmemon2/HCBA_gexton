<?php

namespace App\Http\Controllers\Api\Announcement;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $isChairman = $user->isChairman();
        $isAdmin = $user->role == 'admin';

        $query = Announcement::query()
            ->with(['poster:id,name', 'committee:id,name'])
            ->latest('posted_at');

        if (! $isAdmin) {
            // General and welfare: everyone
            // Committee: only members and chairman of that committee
            $query->where(function ($q) use ($user, $isChairman) {
                $q->whereIn('type', ['general', 'welfare']);

                if ($isChairman) {
                    $committeeIds = $user->committees()
                        ->wherePivot('role', 'chairman')
                        ->pluck('committees.id');
                    $q->orWhere(function ($subQ) use ($committeeIds) {
                        $subQ->where('type', 'committee')
                            ->whereIn('committee_id', $committeeIds);
                    });
                }

                // Also, if user is member of committee
                $memberCommitteeIds = $user->committees()->pluck('committees.id');
                $q->orWhere(function ($subQ) use ($memberCommitteeIds) {
                    $subQ->where('type', 'committee')
                        ->whereIn('committee_id', $memberCommitteeIds);
                });
            });
        }

        $announcements = $query->get();

        return response()->json([
            'status' => true,
            'data' => $announcements,
        ]);
    }

    public function store(AnnouncementRequest $request)
    {
        $user = $request->user();
        $committeeId = null;
        if ($request->hasFile('attachment')) {
            $filename = FileHelper::uploadToPublic($request->file('attachment'), 'assets/announcementAttachment');
        }
        if ($request->type == 'committee') {
            $isAdmin = $user->role == 'admin';

            if (! $isAdmin) {

                $isChairmanOfThisCommittee = $user->committees()
                    ->where('committees.id', $request->committee_id)
                    ->wherePivot('role', 'chairman')
                    ->exists();

                if (! $isChairmanOfThisCommittee) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You are not the chairman of the specified committee',
                    ], 403);
                }
            }
            $committeeId = $request->committee_id;
        }

        DB::beginTransaction();

        try {
            $announcement = Announcement::create([
                'title' => $request->title,
                'type' => $request->type,
                'content' => $request->content,
                'posted_by' => $user->id,
                'posted_at' => now(),
                'committee_id' => $committeeId,
                'attachment' => $filename ?? null
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Announcement created successfully',
                'data' => $announcement->load(['poster:id,name', 'committee:id,name']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // public function show(Request $request, $id)
    // {
    //     $user = $request->user();

    //     $announcement = Announcement::with(['poster:id,name', 'committee:id,name'])->findOrFail($id);

    //     // Check visibility
    //     if (! $this->canViewAnnouncement($user, $announcement)) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Unauthorized',
    //         ], 403);
    //     }

    //     return response()->json([
    //         'status' => true,
    //         'data' => $announcement,
    //     ]);
    // }

    public function update(AnnouncementRequest $request, $id)
    {
        $user = $request->user();
        $announcement = Announcement::findOrFail($id);
        $isAdmin = $user->role == 'admin';

        // Only poster or admin can update
        if ($announcement->posted_by !== $user->id && !$isAdmin) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Handle committee logic only if type is being updated to committee
        if ($request->filled('type') && $request->type === 'committee') {
            if (!$request->has('committee_id')) {
                return response()->json([
                    'status' => false,
                    'message' => 'committee_id is required when updating to committee type',
                ], 400);
            }

            if (!$isAdmin) {
                $isChairmanOfThisCommittee = $user->committees()
                    ->where('committees.id', $request->committee_id)
                    ->wherePivot('role', 'chairman')
                    ->exists();

                if (!$isChairmanOfThisCommittee) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You are not the chairman of the specified committee',
                    ], 403);
                }
            }
        }

        DB::beginTransaction();

        try {
            // Only update fields that are present
            $data = array_filter(
                $request->only(['title', 'type', 'content']),
                fn($value) => ! is_null($value)
            );

            // Handle committee logic only if type is being updated
            if ($request->filled('type')) {
                $data['committee_id'] =
                    $request->type === 'committee'
                    ? $request->committee_id
                    : null;
            }

            $announcement->update($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Announcement updated successfully',
                'data' => $announcement->load(['poster:id,name', 'committee:id,name']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $announcement = Announcement::findOrFail($id);

        // Only poster or admin can delete
        if ($announcement->posted_by != $user->id && $user->role != 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $announcement->delete();

        return response()->json([
            'status' => true,
            'message' => 'Announcement deleted successfully',
        ]);
    }

    private function canViewAnnouncement($user, $announcement)
    {
        if ($user->role == 'admin') {
            return true;
        }

        if ($announcement->type == 'general' || $announcement->type == 'welfare') {
            return true;
        }

        if ($announcement->type == 'committee') {
            return $user->committees()
                ->where('committee_id', $announcement->committee_id)
                ->exists();
        }

        return false;
    }

    public function fetchChairmanCommittees()
    {
        // Committees::
    }
}
