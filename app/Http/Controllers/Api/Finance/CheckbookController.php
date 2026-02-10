<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Checkbook;
use App\Models\Cheque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CheckbookController extends Controller
{

    public function index()
    {
        $checkbooks = Checkbook::with('bankAccount')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'total' => $checkbooks->count(),
            'checkbooks' => $checkbooks
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:accounts,id',
            'name'            => 'required|string|max:50',
            'start_no'        => 'required|integer|min:1',
            'end_no'          => 'required|integer|gt:start_no',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $bankAccount = Account::find($request->bank_account_id);

        if (
            $bankAccount->account_type !== 'asset' ||
            $bankAccount->subtype !== 'bank'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Selected account is not a bank account'
            ], 422);
        }

        $overlap = Checkbook::where('bank_account_id', $request->bank_account_id)
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_no', [$request->start_no, $request->end_no])
                  ->orWhereBetween('end_no', [$request->start_no, $request->end_no])
                  ->orWhere(function ($q) use ($request) {
                      $q->where('start_no', '<=', $request->start_no)
                        ->where('end_no', '>=', $request->end_no);
                  });
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'success' => false,
                'message' => 'Cheque range overlaps with existing checkbook'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $checkbook = Checkbook::create([
                'bank_account_id' => $request->bank_account_id,
                'name' => $request->name,
                'start_no' => $request->start_no,
                'end_no' => $request->end_no,
            ]);

            for ($i = $request->start_no; $i <= $request->end_no; $i++) {
                Cheque::create([
                    'checkbook_id' => $checkbook->id,
                    'cheque_no' => $i,
                    'status' => 'unused'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Checkbook created successfully',
                'checkbook' => $checkbook->load('bankAccount')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error creating checkbook',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show($id)
    {
        $checkbook = Checkbook::with(['bankAccount' , 'cheques:id,checkbook_id,cheque_no,status'])->find($id);

        if (!$checkbook) {
            return response()->json([
                'success' => false,
                'message' => 'Checkbook not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'checkbook' => $checkbook
        ]);
    }
    public function destroy($id)
    {
        $checkbook = Checkbook::find($id);

        if (!$checkbook) {
            return response()->json([
                'success' => false,
                'message' => 'Checkbook not found'
            ], 404);
        }

        $usedCheques = Cheque::where('checkbook_id', $id)
            ->where('status', '!=', 'unused')
            ->exists();

        if ($usedCheques) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete checkbook with used or reserved cheques'
            ], 422);
        }

        DB::transaction(function () use ($checkbook) {
            Cheque::where('checkbook_id', $checkbook->id)->delete();
            $checkbook->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Checkbook deleted successfully'
        ]);
    }
    public function bankAccounts()
    {
        $banks = Account::where('account_type', 'asset')
            ->where('subtype', 'bank')
            ->where('status', 'active')
            ->get(['id', 'account_name', 'account_code']);

        return response()->json([
            'success' => true,
            'banks' => $banks
        ]);
    }
}
