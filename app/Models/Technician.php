<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Technician extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'organization_user_id',
        'first_name',
        'last_name',
        'national_id',
        'phone_number',
        'username',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // Status constants
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    // Get full name
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Get status text
    public function getStatusTextAttribute()
    {
        return $this->status ? 'فعال' : 'غیرفعال';
    }

    // Get status badge class
    public function getStatusBadgeClassAttribute()
    {
        return $this->status ? 'badge-success' : 'badge-danger';
    }

    // Check if has credentials
    public function getHasCredentialsAttribute()
    {
        return !empty($this->username) && !empty($this->password);
    }

    // Get credentials status text
    public function getCredentialsStatusTextAttribute()
    {
        return $this->has_credentials ? 'تعریف شده' : 'تعریف نشده';
    }

    // Get credentials status badge class
    public function getCredentialsStatusBadgeClassAttribute()
    {
        return $this->has_credentials ? 'badge-success' : 'badge-warning';
    }

    // Hash password when setting
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    // Relationship with organization
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Relationship with organization user
    public function organizationUser()
    {
        return $this->belongsTo(OrganizationUser::class);
    }
}