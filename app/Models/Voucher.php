<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no', 'voucher_type_id', 'date', 'description', 'status',
        'created_by', 'approved_by', 'approved_at', 'reversal_of',
        'payment_method', 'cheque_id', 'vendor_id', 'committee_id', 'welfare_request_id'
    ];

    public function type()
    {
        return $this->belongsTo(VoucherType::class, 'voucher_type_id');
    }

    public function entries()
    {
        return $this->hasMany(VoucherEntry::class);
    }

    public function cheque()
    {
        return $this->belongsTo(Cheque::class, 'cheque_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    public function welfare()
    {
        return $this->belongsTo(WelfareClaim::class, 'welfare_request_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function reversal()
    {
        return $this->belongsTo(Voucher::class, 'reversal_of');
    }
}
