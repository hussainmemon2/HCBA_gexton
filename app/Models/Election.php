<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ElectionPosition;
class Election extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'application_fee',
        'submission_fee',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'application_fee' => 'decimal:2',
        'submission_fee' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    public function positions()
    {
        return $this->hasMany(ElectionPosition::class);
    }
    public function applications()
    {
        return $this->hasMany(ElectionApplication::class);
    }

    public function candidates()
    {
        return $this->hasMany(ElectionCandidate::class);
    }
    public function votes()
    {
        return $this->hasMany(ElectionVote::class);
    }
    public function payments()
    {
        return $this->hasMany(ElectionPayment::class);
    }
} 
