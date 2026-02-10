<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_name',
        'account_code',
        'account_type', 
        'subtype', 
        'opening_balance',
        'current_balance',
        'status',
    ];

    public function voucherEntries()
    {
        return $this->hasMany(VoucherEntry::class);
    }

    public function checkbooks()
    {
        return $this->hasMany(CheckBook::class);
    }
}
