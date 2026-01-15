<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditorium extends Model
{
    protected $fillable = [
        'title',
        'description',
        'price'
    ];
    public function booking()
    {
        return $this->hasMany(Booking::class);
    }
}
