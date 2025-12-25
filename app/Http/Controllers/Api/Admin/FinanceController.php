<?php

namespace App\Http\Controllers\Api\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreFinanceTransactionRequest;
use App\Models\FinanceTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function store(StoreFinanceTransactionRequest $request)
    {
        return DB::transaction(function () use ($request) {

            $lastBalance = FinanceTransaction::lockForUpdate()
                ->latest('id')
                ->value('balance_after') ?? 0;

            $amount = (int) $request->amount;

            $balanceAfter = $request->transaction_type === 'funding'
                ? $lastBalance + $amount
                : $lastBalance - $amount;

            if ($balanceAfter < 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient balance'
                ], 422);
            }
            $memberId = null;

            if ($request->source_type === 'annual_fee') {
                $memberId = User::where('cnic', $request->cnic)->value('id');
            }
            $transaction = FinanceTransaction::create([
                'transaction_type'   => $request->transaction_type,
                'source_type'        => $request->source_type,
                'member_id'          => $memberId,
                'committee_id'       => $request->committee_id,
                'welfare_claim_id'   => $request->welfare_claim_id,
                'title'              => $request->title,
                'remarks'            => $request->remarks,
                'amount'             => $amount,
                'balance_before'     => $lastBalance,
                'balance_after'      => $balanceAfter,
                'transaction_date' => now()->toDateString(),
                'created_by'         => $request->user()->id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Transaction created successfully',
                'data' => $transaction
            ], 201);
        });
    }
    public function ledger(Request $request)
    {
        $query = FinanceTransaction::query()->with([
            'member:id,name,cnic',
            'committee:id,name',
            'welfareClaim:id,title'
        ])->orderBy('transaction_date')->orderBy('id');

        // Filter by CNIC
        if ($request->filled('cnic')) {
            $query->whereHas('member', function ($q) use ($request) {
                $q->where('cnic', $request->cnic);
            });
        }

        // Filter by committee
        if ($request->filled('committee_id')) {
            $query->where('committee_id', $request->committee_id);
        }

        // Filter by welfare claim
        if ($request->filled('welfare_claim_id')) {
            $query->where('welfare_claim_id', $request->welfare_claim_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('transaction_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('transaction_date', '<=', $request->to_date);
        }
        $totalFunding =FinanceTransaction::where('transaction_type', 'funding')->sum('amount');
        $totalExpenses =FinanceTransaction::where('transaction_type', 'expense')->sum('amount');
        $availableBalance = $totalFunding - $totalExpenses;

        // Pagination
        $transactions = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'status' => true,
            'total_funding' => $totalFunding,
            'total_expenses' => $totalExpenses,
            'available_balance' => $availableBalance,
            'data' => $transactions->items(), // only transaction items
            'pagination' => [
            'current_page' => $transactions->currentPage(),
            'per_page' => $transactions->perPage(),
            'total' => $transactions->total(),
            'last_page' => $transactions->lastPage(),
            "links"     => $transactions->linkCollection(),
            ],
        ]);
    }
}
