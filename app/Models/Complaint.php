<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Committee;
use App\Models\ComplaintRemark;
use App\Models\ComplaintAttachment;

class Complaint extends Model
{
  protected $fillable = [
        'title',
        'description',
        'committee_id',
        'status',
        'user_satisfied',
        'created_by',
        'closed_at',
    ];

    protected $casts = [
        'user_satisfied' => 'boolean',
        'closed_at' => 'datetime',
    ];

    /** Complaint creator */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Committee */
    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    /** Remarks */
    public function remarks()
    {
        return $this->hasMany(ComplaintRemark::class);
    }

    /** Attachments */
    public function attachments()
    {
        return $this->hasMany(ComplaintAttachment::class);
    }
}
