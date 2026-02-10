<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkbook extends Model
{
    use HasFactory;

    protected $table = 'checkbooks';

    protected $fillable = [
        'bank_account_id',
        'name',
        'start_no',
        'end_no',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(Account::class, 'bank_account_id');
    }

    public function cheques()
    {
        return $this->hasMany(Cheque::class);
    }
}
