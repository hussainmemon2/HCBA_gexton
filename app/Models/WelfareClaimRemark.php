<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WelfareClaimRemark extends Model
{
    protected $fillable = [
        'welfare_claim_id',
        'remark',
    ];

    public function welfareClaim(): BelongsTo
    {
        return $this->belongsTo(WelfareClaim::class);
    }
}
