<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'auditorium_id',
        'title',
        'booking_date',
        'status',
        'booked_by'
    ];
    protected $casts = [
        'booking_date' => 'date'
    ];
    public function auditorium()
    {
        return $this->belongsTo(Auditorium::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function booked_by()
    {
        return $this->belongsTo(User::class);
    }
}
