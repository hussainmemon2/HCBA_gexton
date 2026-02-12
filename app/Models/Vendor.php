<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'description',
        'product',
        'phone'
    ];

    // Relations
    public function vouchers()
    {
        return $this->morphMany(Voucher::class, 'entity');
    }
}
