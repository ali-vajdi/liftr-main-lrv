<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'logo',
        'status',
        'moderator_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // Status constants
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

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

    // Relationship with moderator
    public function moderator()
    {
        return $this->belongsTo(Moderator::class);
    }

    // Relationship with organization users
    public function users()
    {
        return $this->hasMany(OrganizationUser::class);
    }

    // Relationship with organization packages
    public function packages()
    {
        return $this->hasMany(OrganizationPackage::class);
    }

    // Get active packages (multiple)
    public function activePackages()
    {
        return $this->packages()
            ->where('is_active', true)
            ->orderBy('started_at', 'desc')
            ->get();
    }

    // Get the most recent active package (for backward compatibility)
    public function activePackage()
    {
        return $this->activePackages()->first();
    }

    // Get total remaining days (sum of all active packages)
    public function getTotalRemainingDaysAttribute()
    {
        $activePackages = $this->activePackages();
        return $activePackages->sum('remaining_days');
    }

    // Get organization-level status
    public function getOrganizationStatusAttribute()
    {
        $totalRemainingDays = $this->total_remaining_days;
        
        if ($totalRemainingDays <= 0) {
            return 'expired';
        } elseif ($totalRemainingDays <= 7) {
            return 'expiring_soon';
        } else {
            return 'active';
        }
    }

    // Get organization-level status text
    public function getOrganizationStatusTextAttribute()
    {
        switch ($this->organization_status) {
            case 'expired':
                return 'منقضی شده';
            case 'expiring_soon':
                return 'در حال انقضا';
            case 'active':
                return 'فعال';
            default:
                return 'نامشخص';
        }
    }

    // Get organization-level status badge class
    public function getOrganizationStatusBadgeClassAttribute()
    {
        switch ($this->organization_status) {
            case 'expired':
                return 'badge-danger';
            case 'expiring_soon':
                return 'badge-warning';
            case 'active':
                return 'badge-success';
            default:
                return 'badge-secondary';
        }
    }

    // Get package statistics
    public function getPackageStatisticsAttribute()
    {
        $packages = $this->packages()->get();
        
        return [
            'total' => $packages->count(),
            'active' => $packages->where('is_active', true)->count(),
            'expired' => $packages->where('is_active', false)->count(),
            'total_amount_paid' => $packages->sum('package_price'),
        ];
    }
}
