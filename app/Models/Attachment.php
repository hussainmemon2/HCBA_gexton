<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = ['voucher_id', 'attachment', 'attachment_type'];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
