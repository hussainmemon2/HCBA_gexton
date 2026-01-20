<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class ComplaintHistory extends Model
{
    protected $fillable = [
            'complaint_id',
            'from_status',
            'to_status',
            'changed_by',
            'reason',
            'attachment',
        ];

        public function user()
        {
            return $this->belongsTo(User::class, 'changed_by');
        }
        public function complaint()
        {
            return $this->belongsTo(Complaint::class, 'complaint_id');
        }
}