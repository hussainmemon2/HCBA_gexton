<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function trialBalance(Request $request)
    {
        $query = Account::query()->where('status', 'active');

        $accounts = $query->get()->map(function ($account) {
            $debits = VoucherEntry::where('account_id', $account->id)
                ->where('entry_type', 'debit')->sum('amount');

            $credits = VoucherEntry::where('account_id', $account->id)
                ->where('entry_type', 'credit')->sum('amount');

            // Closing balance: Asset/Expense = debit - credit; Liability/Income/Equity = credit - debit
            if (in_array($account->account_type, ['asset', 'expense'])) {
                $closing_balance = $account->opening_balance + $debits - $credits;
            } else {
                $closing_balance = $account->opening_balance + $credits - $debits;
            }

            return [
                'account_id' => $account->id,
                'account_name' => $account->account_name,
                'account_code' => $account->account_code,
                'account_type' => $account->account_type,
                'subtype' => $account->subtype,
                'opening_balance' => $account->opening_balance,
                'total_debit' => $debits,
                'total_credit' => $credits,
                'closing_balance' => $closing_balance,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $accounts
        ]);
    }

    public function ledger(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $account = Account::find($request->account_id);

        if (!$account) {
            return response()->json([
                'status' => false,
                'message' => 'Account not found'
            ], 404);
        }

        $entriesQuery = VoucherEntry::with('voucher')
            ->where('account_id', $account->id)
            ->orderBy('created_at', 'asc');

        if ($request->filled('date_from')) {
            $entriesQuery->whereHas('voucher', function ($q) use ($request) {
                $q->whereDate('date', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $entriesQuery->whereHas('voucher', function ($q) use ($request) {
                $q->whereDate('date', '<=', $request->date_to);
            });
        }

        $balance = $account->opening_balance;
        $entries = $entriesQuery->get()->map(function ($entry) use (&$balance, $account) {
            if (in_array($account->account_type, ['asset', 'expense'])) {
                $balance += $entry->entry_type === 'debit' ? $entry->amount : -$entry->amount;
            } else {
                $balance += $entry->entry_type === 'credit' ? $entry->amount : -$entry->amount;
            }

            return [
                'voucher_no' => $entry->voucher->voucher_no ?? null,
                'date' => $entry->voucher->date ?? null,
                'description' => $entry->voucher->description ?? null,
                'entry_type' => $entry->entry_type,
                'amount' => $entry->amount,
                'balance' => $balance,
            ];
        });

        return response()->json([
            'status' => true,
            'account' => $account->account_name,
            'data' => $entries
        ]);
    }

    public function cashBankBook(Request $request)
    {
      $validator = Validator::make($request->all(), [
            'subtype' => 'required|in:cash,bank',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $accounts = Account::where('subtype', $request->subtype)
            ->where('status', 'active')
            ->get();

        $data = [];

        foreach ($accounts as $account) {
            $balance = $account->opening_balance;

            $entriesQuery = VoucherEntry::with('voucher')
                ->where('account_id', $account->id)
                ->orderBy('created_at', 'asc');

            if ($request->filled('date_from')) {
                $entriesQuery->whereHas('voucher', fn($q) => $q->whereDate('date', '>=', $request->date_from));
            }
            if ($request->filled('date_to')) {
                $entriesQuery->whereHas('voucher', fn($q) => $q->whereDate('date', '<=', $request->date_to));
            }

            $entries = $entriesQuery->get()->map(function ($entry) use (&$balance, $account) {
                if (in_array($account->account_type, ['asset', 'expense'])) {
                    $balance += $entry->entry_type === 'debit' ? $entry->amount : -$entry->amount;
                } else {
                    $balance += $entry->entry_type === 'credit' ? $entry->amount : -$entry->amount;
                }

                return [
                    'voucher_no' => $entry->voucher->voucher_no ?? null,
                    'date' => $entry->voucher->date ?? null,
                    'description' => $entry->voucher->description ?? null,
                    'entry_type' => $entry->entry_type,
                    'amount' => $entry->amount,
                    'balance' => $balance,
                ];
            });

            $data[] = [
                'account_id' => $account->id,
                'account_name' => $account->account_name,
                'entries' => $entries
            ];
        }

        return response()->json([
            'status' => true,
            'subtype' => $request->subtype,
            'data' => $data
        ]);
    }


    // public function profitLoss(Request $request)
    // {
    //    $validator = Validator::make($request->all(), [
    //         'date_from' => 'nullable|date',
    //         'date_to' => 'nullable|date',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     $incomeAccounts = Account::where('account_type', 'income')->get();
    //     $expenseAccounts = Account::where('account_type', 'expense')->get();

    //     $calculate = function ($accounts) use ($request) {
    //         return $accounts->map(function ($account) use ($request) {
    //             $query = VoucherEntry::where('account_id', $account->id);
    //             if ($request->filled('date_from')) {
    //                 $query->whereHas('voucher', fn($q) => $q->whereDate('date', '>=', $request->date_from));
    //             }
    //             if ($request->filled('date_to')) {
    //                 $query->whereHas('voucher', fn($q) => $q->whereDate('date', '<=', $request->date_to));
    //             }

    //             $debits = $query->where('entry_type', 'debit')->sum('amount');
    //             $credits = $query->where('entry_type', 'credit')->sum('amount');

    //             $balance = $account->opening_balance + $credits - $debits;

    //             return [
    //                 'account_id' => $account->id,
    //                 'account_name' => $account->account_name,
    //                 'total_debit' => $debits,
    //                 'total_credit' => $credits,
    //                 'balance' => $balance,
    //             ];
    //         });
    //     };

    //     $income = $calculate($incomeAccounts);
    //     $expense = $calculate($expenseAccounts);

    //     $totalIncome = $income->sum('balance');
    //     $totalExpense = $expense->sum('balance');
    //     $netProfit = $totalIncome - $totalExpense;

    //     return response()->json([
    //         'status' => true,
    //         'income' => $income,
    //         'expense' => $expense,
    //         'total_income' => $totalIncome,
    //         'total_expense' => $totalExpense,
    //         'net_profit' => $netProfit
    //     ]);
    // }


    public function balanceSheet(Request $request)
    {
       $validator = Validator::make($request->all(), [
            'date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $calculateBalance = function ($accounts, $type) use ($request) {
            return $accounts->map(function ($account) use ($request, $type) {
                $query = VoucherEntry::where('account_id', $account->id);
                if ($request->filled('date')) {
                    $query->whereHas('voucher', fn($q) => $q->whereDate('date', '<=', $request->date));
                }

                $debits = $query->where('entry_type', 'debit')->sum('amount');
                $credits = $query->where('entry_type', 'credit')->sum('amount');

                if (in_array($account->account_type, ['asset', 'expense'])) {
                    $closing_balance = $account->opening_balance + $debits - $credits;
                } else {
                    $closing_balance = $account->opening_balance + $credits - $debits;
                }

                return [
                    'account_id' => $account->id,
                    'account_name' => $account->account_name,
                    'account_type' => $account->account_type,
                    'subtype' => $account->subtype,
                    'balance' => $closing_balance,
                ];
            });
        };

        $assets = $calculateBalance(Account::where('account_type', 'asset')->get(), 'asset');
        $liabilities = $calculateBalance(Account::where('account_type', 'liability')->get(), 'liability');
        $equity = $calculateBalance(Account::where('account_type', 'equity')->get(), 'equity');

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equity->sum('balance');

        return response()->json([
            'status' => true,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'check' => $totalAssets == ($totalLiabilities + $totalEquity)
        ]);
    }
}
