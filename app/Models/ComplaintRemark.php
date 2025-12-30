<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Complaint;
use App\Models\User;


class ComplaintRemark extends Model
{
   protected $fillable = [
        'complaint_id',
        'user_id',
        'role',
        'remark',
    ];

    /** Complaint */
    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    /** User who made remark */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
