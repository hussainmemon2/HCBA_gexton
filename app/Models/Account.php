<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_group_id', 'account_code', 'account_name',
        'account_type', 'opening_balance', 'current_balance', 'status',
    ];

    public function group()
    {
        return $this->belongsTo(AccountGroup::class, 'account_group_id');
    }

    public function voucherEntries()
    {
        return $this->hasMany(VoucherEntry::class, 'account_id');
    }
}
