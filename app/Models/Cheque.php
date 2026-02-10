<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    use HasFactory;

    protected $fillable = [
        'checkbook_id',
        'cheque_no',
        'status', 
        'used_for_type', 
        'used_for_id'
    ];

    // Relations
    public function checkbook()
    {
        return $this->belongsTo(CheckBook::class);
    }

    public function voucher()
    {
        return $this->morphTo();
    }
}
