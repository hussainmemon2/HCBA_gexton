<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CommitteeMember;
use App\Models\User;

class Committee extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description'];

    // A committee has many members
    public function members()
    {
        return $this->hasMany(CommitteeMember::class);
    }
    // Optionally, to get users directly
    public function users()
    {
        return $this->belongsToMany(User::class, 'committee_members')
                    ->withPivot('role')
                    ->withTimestamps();
    }
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }
}
