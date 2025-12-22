<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Otp extends Model
{
    protected $fillable = [
        'user_id',
        'identifier',
        'otp',
        'type',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired()
    {
        return now()->greaterThan($this->expires_at);
    }

    public function isUsed()
    {
        return !is_null($this->used_at);
    }
}
