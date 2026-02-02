<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = ['vendor_name', 'contact_no', 'payable_account_id'];

    public function payableAccount()
    {
        return $this->belongsTo(Account::class, 'payable_account_id');
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }
}
