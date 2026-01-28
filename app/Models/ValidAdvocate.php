<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidAdvocate extends Model
{
    protected $fillable = [
        'reg_no',
        'advocate_name',
        'father_name',
        'subdistrict',
        'division',
        'district',
        'hc_date',
        'lc_date',
        'enroll_type',
        'gender',
        'sbc_dues',
        'hcba_dues',
        'status',
    ];
}
