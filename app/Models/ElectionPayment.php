<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectionPayment extends Model
{
    protected $fillable = [
        'election_id',
        'user_id',
        'type',
        'amount',
        'payment_gateway',
        'transaction_id',
        'status'
    ];

    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
