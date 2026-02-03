<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\Checkbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChequebookController extends Controller
{
    public function index()
    {
        $checkbooks = Checkbook::with('bankAccount')->get();

        return response()->json([
            'total_checkbooks' => $checkbooks->count(),
            'checkbooks' => $checkbooks
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:accounts,id',
            'start_no' => 'required|string|max:20',
            'end_no' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $account = Account::find($request->bank_account_id);

        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        if ($account->group->group_name !== 'Banks') {
            return response()->json(['message' => 'Selected account is not a bank account'], 422);
        }

        $checkbook = Checkbook::create([
            'bank_account_id' => $request->bank_account_id,
            'start_no' => $request->start_no,
            'end_no' => $request->end_no
        ]);

        return response()->json([
            'message' => 'Checkbook created successfully',
            'checkbook' => $checkbook->load('bankAccount')
        ], 201);
    }

    public function show($id)
    {
        $checkbook = Checkbook::with('bankAccount')->find($id);

        if (!$checkbook) {
            return response()->json(['message' => 'Checkbook not found'], 404);
        }

        return response()->json($checkbook);
    }

    public function update(Request $request, $id)
    {
        $checkbook = Checkbook::find($id);

        if (!$checkbook) {
            return response()->json(['message' => 'Checkbook not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:accounts,id',
            'start_no' => 'required|string|max:20',
            'end_no' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $account = Account::find($request->bank_account_id);

        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        if ($account->group->group_name !== 'Banks') {
            return response()->json(['message' => 'Selected account is not a bank account'], 422);
        }

        $checkbook->update($request->all());

        return response()->json([
            'message' => 'Checkbook updated successfully',
            'checkbook' => $checkbook->load('bankAccount')
        ]);
    }

    public function destroy($id)
    {
        $checkbook = Checkbook::find($id);

        if (!$checkbook) {
            return response()->json(['message' => 'Checkbook not found'], 404);
        }

        $checkbook->delete();

        return response()->json([
            'message' => 'Checkbook deleted successfully'
        ]);
    }   
    public function bankAccountsDropdown()
    {
        $bankGroup = AccountGroup::where('group_name', 'Banks')->first();

        $bankAccounts = $bankGroup 
            ? Account::where('account_group_id', $bankGroup->id)
                ->where('status', 'active')
                ->get()
            : [];

        return response()->json($bankAccounts);
    }

}
