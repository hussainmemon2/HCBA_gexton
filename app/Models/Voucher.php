<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no',
        'voucher_type', // receipt / payment
        'date',
        'paid_by', // member / other
        'description',
        'status', // draft / pending / approved / rejected
        'payment_method', // cash / cheque / other
        'cheque_id',
        'title',
        'created_by',
        'entity_id',
        'entity_type',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'expense_account_id', // add this
        'asset_account_id',   // add this
   
    ];

    public function entity()
    {
        return $this->morphTo();
    }

    public function cheque()
    {
        return $this->belongsTo(Cheque::class);
    }

    public function entries()
    {
        return $this->hasMany(VoucherEntry::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'voucher_id');
    }
    public function expenseAccount()
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
    }
    public function assetAccount()
    {
        return $this->belongsTo(Account::class, 'asset_account_id');
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

}
