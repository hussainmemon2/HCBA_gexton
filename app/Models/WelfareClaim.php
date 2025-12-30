<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WelfareClaim extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'claimer_id',
        'user_id',
        'type',
        'amount',
        'reason',
        'received_date',
        'approved_date',
        'funding_date',
        'ready_date',
        'collected_date',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'claimer_id' => 'integer',
            'user_id' => 'integer',
            'amount' => 'decimal:2',
            'received_date' => 'date',
            'approved_date' => 'date',
            'funding_date' => 'date',
            'ready_date' => 'date',
            'collected_date' => 'date',
        ];
    }

    // for whom claim is made for
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // claimer one who is making claim for the member
    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimer_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(WelfareClaimAttachment::class);
    }

    public function remarks(): HasMany
    {
        return $this->hasMany(WelfareClaimRemark::class);
    }

    public function financeTransactions()
    {
        return $this->hasMany(FinanceTransaction::class);
    }
}
