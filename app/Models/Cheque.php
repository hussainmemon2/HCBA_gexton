<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_account_id', 'cheque_no', 'status', 
        'voucher_id', 'used_for_type', 'used_for_id', 'used_at'
    ];

    public function checkbook()
    {
        return $this->belongsTo(Checkbook::class);
    }

    public function bank()
    {
        return $this->belongsTo(Account::class, 'bank_account_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
