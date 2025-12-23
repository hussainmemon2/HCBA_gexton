<?php

namespace App\Http\Controllers\Api\Committe;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\CommitteeMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
class CommitteeController extends Controller
{
    public function index()
    {
    $committees = Committee::query()
        ->select('id', 'name', 'description')
        ->withCount('members') 
        ->get();

    return response()->json([
        'status' => true,
        'data'   => $committees
    ], 200);
    }
   public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'users'        => 'nullable|array|min:1',
            'users.*'      => 'integer|exists:users,id',
            'chairman_id'  => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!in_array($request->chairman_id, $request->users)) {
            return response()->json([
                'status' => false,
                'message' => 'Chairman must be included in users list'
            ], 422);
        }

        try {
            DB::transaction(function () use ($request) {

                $committee = Committee::create([
                    'name' => $request->name,
                    'description' => $request->description,
                ]);

                $existingUsers = CommitteeMember::whereIn('user_id', $request->users)
                    ->pluck('user_id')
                    ->toArray();

                if (!empty($existingUsers)) {
                    throw new \Exception(
                        'Users already assigned to a committee: ' . implode(', ', $existingUsers)
                    );
                }

                $members = [];
                foreach ($request->users as $userId) {
                    $members[] = [
                        'committee_id' => $committee->id,
                        'user_id'      => $userId,
                        'role'         => $userId == $request->chairman_id ? 'chairman' : 'member',
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }
                CommitteeMember::insert($members);
            });

            return response()->json([
                'status' => true,
                'message' => 'Committee created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    public function availableUsers()
    {
        $users = User::whereNotIn('id', function ($query) {
                $query->select('user_id')
                    ->from('committee_members');
            })
            ->select('id', 'name', 'email' , 'cnic')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $users
        ], 200);
    }
    public function update(Request $request, $id)
    {
    $validator = Validator::make($request->all(), [
        'name'         => 'required|string|max:255',
        'description'  => 'nullable|string',
        'users'        => 'required|array|min:1',
        'users.*'      => 'integer|exists:users,id',
        'chairman_id'  => 'required|integer|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    if (!in_array($request->chairman_id, $request->users)) {
        return response()->json([
            'status' => false,
            'message' => 'Chairman must be included in users list'
        ], 422);
    }

    try {
        DB::transaction(function () use ($request, $id) {

            $committee = Committee::findOrFail($id);

            // Update committee info
            $committee->update([
                'name'        => $request->name,
                'description' => $request->description,
            ]);

            // Current committee users
            $currentUsers = CommitteeMember::where('committee_id', $committee->id)
                ->pluck('user_id')
                ->toArray();

            $newUsers = $request->users;

            // Users to add & remove
            $usersToAdd    = array_diff($newUsers, $currentUsers);
            $usersToRemove = array_diff($currentUsers, $newUsers);

            if (!empty($usersToAdd)) {
                $alreadyAssigned = CommitteeMember::whereIn('user_id', $usersToAdd)
                    ->where('committee_id', '!=', $committee->id)
                    ->pluck('user_id')
                    ->toArray();

                if (!empty($alreadyAssigned)) {
                    throw new \Exception(
                        'Users already assigned to another committee: ' . implode(', ', $alreadyAssigned)
                    );
                }
            }

            $insertData = [];
            foreach ($usersToAdd as $userId) {
                $insertData[] = [
                    'committee_id' => $committee->id,
                    'user_id'      => $userId,
                    'role'         => $userId == $request->chairman_id ? 'chairman' : 'member',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }

            if (!empty($insertData)) {
                CommitteeMember::insert($insertData);
            }

            if (!empty($usersToRemove)) {
                CommitteeMember::where('committee_id', $committee->id)
                    ->whereIn('user_id', $usersToRemove)
                    ->delete();
            }

            CommitteeMember::where('committee_id', $committee->id)
                ->update(['role' => 'member']);

            CommitteeMember::where('committee_id', $committee->id)
                ->where('user_id', $request->chairman_id)
                ->update(['role' => 'chairman']);
        });

        return response()->json([
            'status' => true,
            'message' => 'Committee updated successfully'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 400);
    }
    
    }
public function view($id)
{
    $committee = Committee::query()
        ->select('id', 'name', 'description')
        ->with([
            'members' => function ($q) {
                $q->select('id', 'committee_id', 'user_id', 'role');
            },
            'members.user' => function ($q) {
                $q->select(
                    'id',
                    'name',
                    'email',
                    'passport_image'
                );
            }
        ])
        ->find($id);

    if (!$committee) {
        return response()->json([
            'status' => false,
            'message' => 'Committee not found'
        ], 404);
    }

    return response()->json([
        'status' => true,
        'data'   => $committee
    ], 200);
}


}
