<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Complaint;
use App\Models\User;
class ComplaintAttachment extends Model
{
 protected $fillable = [
        'complaint_id',
        'uploaded_by',
        'filename',
        'file_path',
    ];

    /** Complaint */
    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    /** Uploader */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
