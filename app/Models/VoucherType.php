<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'requires_proof'];

    protected $casts = [
        'requires_proof' => 'boolean',
    ];
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }
}
