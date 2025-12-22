<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Committee;
use App\Models\User;
class CommitteeMember extends Model
{
    use HasFactory;
    protected $fillable = ['committee_id', 'user_id', 'role'];

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
