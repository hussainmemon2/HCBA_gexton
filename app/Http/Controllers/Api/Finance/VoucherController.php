<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\{
    Voucher,
    VoucherEntry,
    Account,
    Cheque,
    AuditLog,
    Checkbook,
    Committee,
    User,
    Vendor,
    WelfareClaim
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $query = Voucher::query()->with(['entries.account', 'attachments', 'entity']);

        // Optional filters
        if ($request->filled('voucher_type')) {
            $query->where('voucher_type', $request->voucher_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $vouchers = $query->orderBy('date', 'desc')->paginate(20); // paginate 20 per page

        return response()->json([
            'status' => true,
            'data' => $vouchers
        ]);
    }

    public function show($id)
    {
        $voucher = Voucher::with([
            'entries.account',
            'attachments',
            'entity',
            'creator',
            'approver',
            'rejecter'
        ])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $voucher
        ]);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'voucher_type'       => 'required|in:receipt,payment',
            'date'               => 'required|date',
            'amount'             => 'required|numeric|min:0.01',
            'asset_account_id'   => 'required|exists:accounts,id',
            'paid_by'            => 'required_if:voucher_type,receipt|in:member,other',
            'expense_account_id' => 'required_if:voucher_type,payment|exists:accounts,id',
            'payment_method'     => 'required_if:voucher_type,payment|in:cash,cheque,other',
            'cheque_id'          => 'required_if:payment_method,cheque|exists:cheques,id',
            'entity_id'          => 'nullable|integer',
            'entity_type'        => 'nullable|string',
            'title'              => 'nullable|string',
            'description'        => 'nullable|string',
            'attachments.*'      => 'file'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {

            $assetAccount = Account::lockForUpdate()->findOrFail($request->asset_account_id);

            if ($assetAccount->account_type !== 'asset') {
                return response()->json([
                    'status' => false,
                    'message' => 'Selected account is not an asset account'
                ], 422);
            }

            if ($request->voucher_type === 'payment' && $request->expense_account_id) {
                $expenseAccount = Account::findOrFail($request->expense_account_id);
                if ($expenseAccount->account_type !== 'expense') {
                    return response()->json([
                        'status' => false,
                        'message' => 'Selected account is not an expense account'
                    ], 422);
                }

                if (in_array($expenseAccount->subtype, ['vendor', 'committee', 'welfare']) && !$request->entity_id) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Entity is required for selected expense subtype'
                    ], 422);
                }
            }

            if ($request->voucher_type === 'receipt' && $request->paid_by === 'member' && !$request->entity_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Member entity is required for paid_by member'
                ], 422);
            }
                $entitytype = null;
                if($request->voucher_type === 'receipt' && $request->paid_by === 'member'){
                    $entitytype = 'member';
                }else if($request->voucher_type === 'payment' && in_array($expenseAccount->subtype, ['vendor', 'committee', 'welfare'])){
                    $entitytype = $expenseAccount->subtype;
                }
            $voucher = Voucher::create([
                'voucher_no'     => $this->generateVoucherNo(),
                'voucher_type'   => $request->voucher_type,
                'date'           => $request->date,
                'paid_by'        => $request->paid_by,
                'title'          => $request->title,
                'description'    => $request->description,
                'payment_method' => $request->payment_method,
                'cheque_id'      => $request->cheque_id,
                'entity_id'      => $request->entity_id,
                'entity_type'    => $entitytype,
                'created_by'     => $request->user()->id,
                'status'         => 'pending'
            ]);

            if ($voucher->voucher_type === 'receipt') {
                $this->handleReceipt($voucher, $assetAccount, $request);
            }

            if ($voucher->voucher_type === 'payment') {
                $this->handlePayment($voucher, $assetAccount, $request);
            }

            if ($request->hasFile('attachments')) {

                $folderPath = public_path('uploads/vouchers/' . $voucher->id);

                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0755, true);
                }

                foreach ($request->file('attachments') as $file) {
                    $fileName = uniqid('att_') . '.' . $file->getClientOriginalExtension();
                    $file->move($folderPath, $fileName);
                    $voucher->attachments()->create([
                        'attachment'      => 'uploads/vouchers/' . $voucher->id . '/' . $fileName,
                        'attachment_type' => $file->getClientOriginalExtension(),
                    ]);
                }
            }


            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'voucher_created',
                'reference_type' => 'voucher',
                'reference_id' => $voucher->id,
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Voucher created successfully',
                'voucher_id' => $voucher->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function handleReceipt(Voucher $voucher, Account $assetAccount, Request $request)
    {
        $amount = (int) $request->amount;

        VoucherEntry::create([
            'voucher_id' => $voucher->id,
            'account_id' => $assetAccount->id,
            'entry_type' => 'debit',
            'amount'     => $amount
        ]);

        $incomeAccount = Account::where('account_type', 'income')
            ->where('status', 'active')
            ->firstOrFail();

        VoucherEntry::create([
            'voucher_id' => $voucher->id,
            'account_id' => $incomeAccount->id,
            'entry_type' => 'credit',
            'amount'     => $amount
        ]);
    }

    private function handlePayment(Voucher $voucher, Account $assetAccount, Request $request)
    {
       $amount = (int) $request->amount;

        $expenseAccount = Account::lockForUpdate()->findOrFail($request->expense_account_id);
        if ($assetAccount->current_balance  < $amount) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient balance in asset account'
            ], 422);
        }
        VoucherEntry::create([
            'voucher_id' => $voucher->id,
            'account_id' => $expenseAccount->id,
            'entry_type' => 'debit',
            'amount'     => $amount
        ]);

        VoucherEntry::create([
            'voucher_id' => $voucher->id,
            'account_id' => $assetAccount->id,
            'entry_type' => 'credit',
            'amount'     => $amount
        ]);

        if ($request->payment_method === 'cheque' && $request->cheque_id) {
            $cheque = Cheque::where('id', $request->cheque_id)
                ->where('bank_account_id', $assetAccount->id)
                ->where('status', 'unused')
                ->lockForUpdate()
                ->firstOrFail();

            $cheque->update([
                'status' => 'reserved',
                'used_for_id' => $voucher->id,
                'used_for_type' => Voucher::class
            ]);
        }
    }

    private function generateVoucherNo()
    {
        $last = Voucher::latest('id')->first();
        $next = $last ? $last->id + 1 : 1;
        return 'VCH-' . now()->format('Ymd') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function approve(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $voucher = Voucher::lockForUpdate()->findOrFail($id);
            if ($voucher->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Only pending vouchers can be approved'
                ], 422);
            }
            $entries = $voucher->entries()->lockForUpdate()->get();
            if ($entries->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Voucher has no entries'
                ], 422);
            }

            foreach ($entries as $entry) {

                $account = Account::lockForUpdate()->findOrFail($entry->account_id);

                $normalDebitTypes = ['asset', 'expense'];

                if ($entry->entry_type === 'credit') {

                    // If asset or expense credited → balance will decrease
                    if (in_array($account->account_type, $normalDebitTypes)) {

                        if ($account->current_balance < $entry->amount) {
                            throw new \Exception(
                                "Insufficient balance in account: {$account->account_name}"
                            );
                        }
                    }
                }

                if ($entry->entry_type === 'debit') {

                    // If income/liability/equity debited → balance will decrease
                    if (!in_array($account->account_type, $normalDebitTypes)) {

                        if ($account->current_balance < $entry->amount) {
                            throw new \Exception(
                                "Insufficient balance in account: {$account->account_name}"
                            );
                        }
                    }
                }
            }

            foreach ($entries as $entry) {

                $account = Account::lockForUpdate()->findOrFail($entry->account_id);

                $normalDebitTypes = ['asset', 'expense'];

                if ($entry->entry_type === 'debit') {

                    if (in_array($account->account_type, $normalDebitTypes)) {
                        // Asset & Expense increase on debit
                        $account->current_balance += $entry->amount;
                    } else {
                        // Income, Liability, Equity decrease on debit
                        $account->current_balance -= $entry->amount;
                    }

                } else { // credit

                    if (in_array($account->account_type, $normalDebitTypes)) {
                        // Asset & Expense decrease on credit
                        $account->current_balance -= $entry->amount;
                    } else {
                        // Income, Liability, Equity increase on credit
                        $account->current_balance += $entry->amount;
                    }
                }

                $account->save();
            }


            if ($voucher->entity_type === 'welfare' && $voucher->entity_id) {

                $welfare = WelfareClaim::lockForUpdate()->find($voucher->entity_id);

                if ($welfare) {
                    $debitAmount = $entries->where('entry_type', 'debit')->sum('amount');

                    $welfare->update([
                        'status' => 'ready',
                        'amount' => $debitAmount
                    ]);
                }
            }


            if (
                $voucher->voucher_type === 'payment' &&
                $voucher->payment_method === 'cheque' &&
                $voucher->cheque_id
            ) {
                $cheque = Cheque::where('id', $voucher->cheque_id)
                    ->where('status', 'reserved')
                    ->lockForUpdate()
                    ->first();

                if ($cheque) {
                    $cheque->update([
                        'status' => 'used',
                        'used_for_id' => $voucher->id,
                        'used_for_type' => Voucher::class
                    ]);
                }
            }


            $voucher->update([
                'status' => 'approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now()
            ]);


            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'voucher_approved',
                'reference_type' => 'voucher',
                'reference_id' => $voucher->id,
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Voucher approved successfully'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function reject(Request $request, $id)
    {
       $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $voucher = Voucher::lockForUpdate()->findOrFail($id);

            if ($voucher->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Only pending vouchers can be rejected'
                ], 422);
            }

            // Release cheque if payment method is cheque
            if ($voucher->payment_method === 'cheque' && $voucher->cheque_id) {
                $cheque = Cheque::lockForUpdate()->find($voucher->cheque_id);
                if ($cheque) {
                    $cheque->update([
                        'status' => 'unused',
                        'used_for_id' => null,
                        'used_for_type' => null
                    ]);
                }
            }

            // Update voucher status
            $voucher->update([
                'status' => 'rejected',
                'rejected_by' => $request->user()->id,
                'rejected_at' => now(),
                'rejection_reason' => $request->reason
            ]);

            // Audit log
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'voucher_rejected',
                'reference_type' => 'voucher',
                'reference_id' => $voucher->id,
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Voucher rejected successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getEntitiesBySubtype(Request $request)
    {
       $validator = Validator::make($request->all(), [
            'type' => 'required|in:vendor,committee,welfare'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        switch ($request->type) {
            case 'vendor':
                $entities =  Vendor::select('id', 'name')->get();
                break;
            case 'committee':
                $entities =  Committee::select('id', 'name')->get();
                break;

            case 'welfare':
                $entities = WelfareClaim::select('id', 'name')->where('status' , 'funding')->get();
                break;

            default:
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid subtype'
                ], 422);
        }

        return response()->json([
            'status' => true,
            'data' => $entities
        ]);
    }
    public function getCheckbooksByBank(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:accounts,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $account = Account::findOrFail($request->bank_account_id);

        if ($account->account_type !== 'asset' || $account->subtype !== 'bank') {
            return response()->json([
                'status' => false,
                'message' => 'Selected account is not a bank asset account'
            ], 422);
        }

        $checkbooks = Checkbook::where('bank_account_id', $account->id)
            ->select('id', 'name')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $checkbooks
        ]);
    }

    public function getUnusedChequesByCheckbook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'checkbook_id' => 'required|exists:checkbooks,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $cheques = Cheque::where('checkbook_id', $request->checkbook_id)
            ->where('status', 'unused')
            ->orderBy('cheque_no')
            ->select('id', 'cheque_no')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $cheques
        ]);
    }

    public function getAssetAccountsByPaymentMethod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:cash,cheque,bank'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Account::where('account_type', 'asset')
            ->where('status', 'active');

        if ($request->payment_method === 'cash') {
            $query->where('subtype', 'cash');
        }

        if ($request->payment_method === 'cheque' || $request->payment_method === 'bank') {
            $query->where('subtype', 'bank');
        }

        $accounts = $query->select('id', 'account_name','account_code', 'current_balance')->get();

        return response()->json([
            'status' => true,
            'data' => $accounts
        ]);
    }

    public function getExpenseAccounts()
    {
        $accounts = Account::where('account_type', 'expense')
            ->where('status', 'active')
            ->select('id', 'account_name', 'account_code', 'account_type', 'subtype')
            ->orderBy('account_name')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $accounts
        ]);
    }
}
