<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WelfareClaimAttachment extends Model
{
    protected $fillable = [
        'welfare_claim_id',
        'filename',
    ];

    public function welfareClaim(): BelongsTo
    {
        return $this->belongsTo(WelfareClaim::class);
    }
}
