<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountGroupController extends Controller
{
    public function index()
    {
        $groups = AccountGroup::whereNull('parent_id')
            ->with([
                'children' => function ($q) {
                    $q->withCount('accounts')
                    ->with(['children' , 'accounts']); // future proof (3 levels)
                },
                'accounts'
            ])
            ->withCount('accounts')
            ->get();

        $totalAccounts = Account::count();

        return response()->json([
            'total_accounts' => $totalAccounts,
            'groups' => $groups
        ]);
    }

    public function store(Request $request)
    {
       $validator =  Validator::make($request->all(), [
            'group_name' => 'required|string|max:100',
            'parent_id'  => 'nullable|exists:account_groups,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $group = AccountGroup::create($request->only('group_name','parent_id'));

        return response()->json($group, 201);
    }

    public function update(Request $request, $id)
    {
        $group = AccountGroup::find($id);
        if (!$group) {
            return response()->json(['message' => 'Account Group not found'], 404);
        }
       $validator = Validator::make($request->all(), [
            'group_name' => 'required|string|max:100',
            'parent_id'  => 'nullable|exists:account_groups,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $group->update($request->only('group_name','parent_id'));

        return response()->json($group);
    }

    public function destroy($id)
    {
        $group = AccountGroup::find($id);

        if (!$group) {
            return response()->json(['message' => 'Account Group not found'], 404);
        }

        if ($group->accounts()->exists()) {
            return response()->json([
                'message' => 'Cannot delete group with accounts'
            ], 422);
        }

        $group->delete();
        return response()->json(['message' => 'Deleted']);
    }
}

