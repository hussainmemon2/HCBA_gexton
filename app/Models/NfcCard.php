<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\models\User;

class NfcCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_uid',
        'status',
        'issued_at',
        'blocked_at',
        'remarks'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'blocked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }
    public function canToggleStatus()
    {
     return $this->status !== 'blocked';
    }


}
