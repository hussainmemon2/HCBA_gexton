<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
Use App\Models\Otp;
Use App\Models\Committee;
Use App\Models\Complaint;
Use App\Models\ComplaintRemark;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'proposer_name',
        'seconder_name',
        'name',
        'guardian_name',
        'date_of_birth',
        'gender',
        'caste',
        'cnic',
        'bar_license_number',
        'cnic_front_path',
        'idcard_of_highcourt_path',
        'license_ofhighcourt_path',
        'passport_image',
        'present_address',
        'permanent_address',
        'office_address',
        'date_of_enrollment_as_advocate',
        'date_of_enrollment_as_advocate_high_court',
        'district_bar_member',
        'other_bar_member',
        'email',
        'phone',
        'password',
        'role_id',
        'is_verified_nadra',
        'is_verified_hcb',
        'status',
        'dues_paid',
        'email_verified',
        'email_verified_at',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function otps()
    {
        return $this->hasMany(Otp::class);
    }
    public function committees()
    {
    return $this->belongsToMany(Committee::class, 'committee_members')
    ->withPivot('role')
    ->withTimestamps();
    }
    public function complaints()
    {
      return $this->hasMany(Complaint::class, 'created_by');
    }

    public function complaintRemarks()
    {
        return $this->hasMany(ComplaintRemark::class);
    }
}
