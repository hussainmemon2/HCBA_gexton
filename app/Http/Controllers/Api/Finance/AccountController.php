<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::with('group:id,group_name')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'total_accounts' => $accounts->count(),
            'accounts' => $accounts
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_name' => 'required|string|max:150',
            'account_code' => 'required|string|max:20|unique:accounts,account_code',
            'account_group_id' => 'required|exists:account_groups,id',
            'account_type' => 'required|in:asset,liability,expense,income',
            'opening_balance' => 'nullable|numeric',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $account = Account::create([
            'account_name' => $request->account_name,
            'account_code' => $request->account_code,
            'account_group_id' => $request->account_group_id,
            'account_type' => $request->account_type,
            'opening_balance' => $request->opening_balance ?? 0,
            'current_balance' => $request->opening_balance ?? 0,
            'status' => $request->status ?? 'active',
        ]);

        return response()->json([
            'message' => 'Account created successfully',
            'account' => $account->load('group')
        ], 201);
    }

    public function show($id)
    {
        $account = Account::with('group:id,group_name')->find($id);
        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }
        return response()->json($account);
    }

    public function update(Request $request, $id)
    {
        $account = Account::find($id);
        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'account_name' => 'required|string|max:150',
            'account_code' => 'required|string|max:20|unique:accounts,account_code,' . $id,
            'account_group_id' => 'required|exists:account_groups,id',
            'account_type' => 'required|in:asset,liability,expense,income',
            'opening_balance' => 'nullable|numeric',
            'current_balance' => 'nullable|numeric',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $account->update([
            'account_name' => $request->account_name,
            'account_code' => $request->account_code,
            'account_group_id' => $request->account_group_id,
            'account_type' => $request->account_type,
            'opening_balance' => $request->opening_balance ?? $account->opening_balance,
            'current_balance' => $request->current_balance ?? $account->current_balance,
            'status' => $request->status ?? $account->status,
        ]);

        return response()->json([
            'message' => 'Account updated successfully',
            'account' => $account->load('group')
        ]);
    }

    public function destroy($id)
    {
        $account = Account::find($id);
        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }
        $account->delete();
        return response()->json([
            'message' => 'Account deleted successfully'
        ]);
    }
    public function toggleStatus($id)
    {
        $account = Account::find($id);

        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        $account->status = $account->status === 'active' ? 'inactive' : 'active';
        $account->save();

        return response()->json([
            'message' => 'Account status updated successfully',
            'account' => $account
        ]);
    }
}
