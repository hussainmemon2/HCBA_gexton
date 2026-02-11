<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function index(Request $request)
    {
    $query = Account::orderBy('account_type')
        ->orderBy('subtype');
    if ($request->user()->role !== "admin") {
        $query->where('status', 'active');
    }

    $accounts = $query->get();
        $grouped = $accounts->groupBy('account_type')->map(function ($accountsByType) {
            return $accountsByType->groupBy('subtype')->map(function ($accountsBySubtype) {
                return $accountsBySubtype->map(function ($account) {
                    return [
                        'id' => $account->id,
                        'name' => $account->account_name,
                        'code' => $account->account_code,
                        'current_balance' => $account->current_balance,
                        'status'    =>$account->status
                    ];
                })->values(); 
            })->toArray();
        })->toArray();

        return response()->json([
            'success' => true,
            'total_accounts' => $accounts->count(),
            'accounts' => $grouped
        ]);
    }

    public function show($id)
    {
        $account = Account::find($id);

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'account' => $account
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_name' => 'required|string|max:150',
            'account_code' => 'required|string|max:20|unique:accounts,account_code',
            'account_type' => 'required|in:asset,liability,income,expense,equity',
            'subtype' => 'nullable|string|max:50',
            'opening_balance' => 'required|numeric|min:0',
            'status' => 'nullable|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $type = $request->account_type;
        $subtype = $request->subtype;

        $allowedSubtypes = [
            'asset'     => ['cash', 'bank', 'other'],
            'expense'   => ['vendor', 'committee', 'welfare', 'other'],
            'income'    => [null],
            'liability' => [null],
            'equity'    => [null],
        ];

        if ($subtype !== null && !in_array($subtype, $allowedSubtypes[$type])) {
            return response()->json([
                'success' => false,
                'message' => "Invalid subtype for account type {$type}. Allowed: " .
                    implode(', ', array_filter($allowedSubtypes[$type]))
            ], 422);
        }

        $openingBalance = $request->opening_balance ?? 0;

        $account = Account::create([
            'account_name'     => $request->account_name,
            'account_code'     => $request->account_code,
            'account_type'     => $type,
            'subtype'          => $subtype,
            'opening_balance'  => $openingBalance,
            'current_balance'  => $openingBalance,
            'status'           => $request->status ?? 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'account' => $account
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $account = Account::find($id);
        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'account_name' => 'required|string|max:150',
            'account_code' => 'required|string|max:20|unique:accounts,account_code,' . $id,
            'account_type' => 'required|in:asset,liability,income,expense,equity',
            'subtype' => 'nullable|string|max:50',
            'opening_balance' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $type = $request->account_type;
        $subtype = $request->subtype;

        $allowedSubtypes = [
            'asset' => ['cash', 'bank', 'other'],
            'expense' => ['vendor', 'committee', 'welfare', 'other'],
            'income' => [null],
            'liability' => [null],
            'equity' => [null],
        ];

        if ($subtype !== null && !in_array($subtype, $allowedSubtypes[$type])) {
            return response()->json([
                'success' => false,
                'message' => "Invalid subtype for account type $type. Allowed: " . implode(', ', $allowedSubtypes[$type])
            ], 422);
        }

        if ($account->voucherEntries()->exists() && $request->has('opening_balance')) {
            return response()->json([
                'success' => false,
                'message' => 'Opening balance cannot be changed after transactions exist'
            ], 422);
        }

        $currentBalance = $account->voucherEntries()->exists() 
            ? $account->current_balance 
            : ($request->opening_balance ?? $account->opening_balance);

        $account->update([
            'account_name' => $request->account_name,
            'account_code' => $request->account_code,
            'account_type' => $type,
            'subtype' => $subtype,
            'opening_balance' => $request->opening_balance ?? $account->opening_balance,
            'current_balance' => $currentBalance,
            'status' => $request->status ?? $account->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Account updated successfully',
            'account' => $account
        ]);
    }

    public function destroy($id)
    {
        $account = Account::find($id);

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found'
            ], 404);
        }

        if ($account->voucherEntries()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete account linked with vouchers'
            ], 422);
        }

        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);
    }

}
