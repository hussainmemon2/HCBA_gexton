<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectionCandidate extends Model
{
    protected $fillable = [
        'election_id',
        'position_id',
        'user_id',
        'is_winner'
    ];

    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    public function position()
    {
        return $this->belongsTo(ElectionPosition::class, 'position_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function votes()
    {
        return $this->hasMany(ElectionVote::class, 'candidate_id');
    }
}
