<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Committee;
use App\Models\User;

class ComplaintTransfer extends Model
{
      protected $fillable = [
        'complaint_id',
        'from_committee_id',
        'to_committee_id',
        'transferred_by',
        'reason',
    ];

    public function fromCommittee()
    {
        return $this->belongsTo(Committee::class, 'from_committee_id');
    }

    public function toCommittee()
    {
        return $this->belongsTo(Committee::class, 'to_committee_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}