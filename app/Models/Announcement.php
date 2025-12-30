<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'type',
        // 'role',
        'content',
        'posted_by',
        'posted_at',
        'committee_id',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
    ];

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }
}