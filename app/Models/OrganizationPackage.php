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
        'payment_status',
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

    // Payment status constants
    const PAYMENT_STATUS_UNPAID = 'unpaid';
    const PAYMENT_STATUS_PARTIALLY_PAID = 'partially_paid';
    const PAYMENT_STATUS_FULLY_PAID = 'fully_paid';

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

    public function payments()
    {
        return $this->hasMany(PackagePayment::class);
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

    // Payment methods
    public function getTotalPaidAmountAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getRemainingAmountAttribute()
    {
        return max(0, $this->package_price - $this->total_paid_amount);
    }

    public function getPaymentStatusTextAttribute()
    {
        return match($this->payment_status) {
            self::PAYMENT_STATUS_FULLY_PAID => 'پرداخت کامل',
            self::PAYMENT_STATUS_PARTIALLY_PAID => 'پرداخت جزئی',
            self::PAYMENT_STATUS_UNPAID => 'پرداخت نشده',
            default => 'نامشخص',
        };
    }

    public function getPaymentStatusBadgeClassAttribute()
    {
        return match($this->payment_status) {
            self::PAYMENT_STATUS_FULLY_PAID => 'badge-success',
            self::PAYMENT_STATUS_PARTIALLY_PAID => 'badge-warning',
            self::PAYMENT_STATUS_UNPAID => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function getFormattedTotalPaidAmountAttribute()
    {
        return number_format($this->total_paid_amount, 0) . ' تومان';
    }

    public function getFormattedRemainingAmountAttribute()
    {
        return number_format($this->remaining_amount, 0) . ' تومان';
    }

    /**
     * Update payment status based on total paid amount
     */
    public function updatePaymentStatus()
    {
        $totalPaid = $this->total_paid_amount;
        
        if ($totalPaid >= $this->package_price) {
            $this->payment_status = self::PAYMENT_STATUS_FULLY_PAID;
        } elseif ($totalPaid > 0) {
            $this->payment_status = self::PAYMENT_STATUS_PARTIALLY_PAID;
        } else {
            $this->payment_status = self::PAYMENT_STATUS_UNPAID;
        }
        
        $this->save();
    }

    /**
     * Check if package can accept partial payment
     */
    public function canAcceptPartialPayment()
    {
        return $this->package_duration_days > 30;
    }
}