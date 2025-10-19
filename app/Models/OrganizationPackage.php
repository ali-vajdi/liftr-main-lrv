<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class OrganizationPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'package_id',
        'package_name',
        'package_duration_days',
        'package_duration_label',
        'package_price',
        'started_at',
        'is_active',
        'moderator_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'is_active' => 'boolean',
        'package_price' => 'decimal:2',
    ];

    // Status constants
    const STATUS_ACTIVE = true;
    const STATUS_INACTIVE = false;

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function moderator()
    {
        return $this->belongsTo(Moderator::class);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'فعال' : 'غیرفعال';
    }

    public function getStatusBadgeClassAttribute()
    {
        if (!$this->is_active) {
            return 'badge-danger';
        }

        $remainingDays = $this->remaining_days;
        if ($remainingDays <= 0) {
            return 'badge-danger';
        } elseif ($remainingDays <= 7) {
            return 'badge-warning';
        } else {
            return 'badge-success';
        }
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->package_price) . ' تومان';
    }

    // Get remaining days (calculated)
    public function getRemainingDaysAttribute()
    {
        if (!$this->is_active) {
            return 0;
        }

        $now = Carbon::now();
        $expiresAt = $this->started_at->copy()->addDays($this->package_duration_days);
        
        // If already expired, return 0
        if ($now->gte($expiresAt)) {
            return 0;
        }
        
        // Calculate remaining days more precisely using hours
        $remainingHours = $now->diffInHours($expiresAt, false);
        $remainingDays = ceil($remainingHours / 24); // Round up to get full days
        return max(0, $remainingDays);
    }

    public function getIsExpiredAttribute()
    {
        return $this->remaining_days <= 0;
    }

    // Get calculated expiry date
    public function getExpiresAtAttribute()
    {
        return $this->started_at->copy()->addDays($this->package_duration_days);
    }

    // Get current package information (from the original package)
    public function getCurrentPackageInfoAttribute()
    {
        if (!$this->package) {
            return null;
        }

        return [
            'name' => $this->package->name,
            'duration_days' => $this->package->duration_days,
            'duration_label' => $this->package->duration_label,
            'price' => $this->package->price,
            'formatted_price' => $this->package->formatted_price,
            'is_public' => $this->package->is_public,
        ];
    }

    // Get assigned package information (stored at assignment time)
    public function getAssignedPackageInfoAttribute()
    {
        return [
            'name' => $this->package_name,
            'duration_days' => $this->package_duration_days,
            'duration_label' => $this->package_duration_label,
            'price' => $this->package_price,
            'formatted_price' => $this->formatted_price,
        ];
    }

    // Check if package information has changed since assignment
    public function getHasPackageChangedAttribute()
    {
        if (!$this->package) {
            return false;
        }

        return $this->package->name !== $this->package_name ||
               $this->package->duration_days !== $this->package_duration_days ||
               $this->package->duration_label !== $this->package_duration_label ||
               $this->package->price != $this->package_price;
    }
}