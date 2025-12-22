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
    

}
