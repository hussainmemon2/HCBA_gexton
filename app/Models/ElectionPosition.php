<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Election;

class ElectionPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'election_id',
        'title',
        'submission_price',
        'min_experience'
    ];


    public function election()
    {
        return $this->belongsTo(Election::class);
    }
    public function applications()
    {
        return $this->hasMany(ElectionApplication::class, 'position_id');
    }

    public function candidates()
    {
        return $this->hasMany(ElectionCandidate::class, 'position_id');
    }

    public function votes()
    {
        return $this->hasMany(ElectionVote::class, 'position_id');
    }
}
