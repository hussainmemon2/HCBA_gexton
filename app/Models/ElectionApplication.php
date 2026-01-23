<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectionApplication extends Model
{
    protected $fillable = [
        'election_id',
        'position_id',
        'vakalatnama',
        'case_order',
        'fee_challan_of_bar_card',
        'bar_certificate',
        'no_dues_cert_from_high_court',
        'no_dues_cert_from_sindh_bar',
        'user_id',
        'application_fee_paid',
        'submission_fee_paid',
        'status'
    ];

    protected $casts = [
        'application_fee_paid' => 'boolean',
        'submission_fee_paid' => 'boolean',
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
}
