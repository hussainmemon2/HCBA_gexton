<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
Use App\Models\User;
Use App\Models\Committee;
Use App\Models\WelfareClaim;

class FinanceTransaction extends Model
{
  protected $guarded = [];
     // Member (users table)
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    // Committee
    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    // Welfare Claim
    public function welfareClaim()
    {
        return $this->belongsTo(WelfareClaim::class);
    }

    // Created By (Admin / Staff)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }



    public function scopeFunding($query)
    {
        return $query->where('transaction_type', 'funding');
    }

    public function scopeExpense($query)
    {
        return $query->where('transaction_type', 'expense');
    }

    public function scopeCommitteeExpense($query)
    {
        return $query->where('source_type', 'committee_expense');
    }

    public function scopeWelfareExpense($query)
    {
        return $query->where('source_type', 'welfare_expense');
    }
}
