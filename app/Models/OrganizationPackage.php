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
        'paid_periods',
        'started_at',
        'is_active',
        'moderator_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'is_active' => 'boolean',
        'package_price' => 'decimal:2',
        'paid_periods' => 'array',
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

    public function periods()
    {
        return $this->hasMany(PackagePeriod::class)->orderBy('period_number');
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
        return number_format((float)$this->package_price, 0) . ' تومان';
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

    /**
     * Get current period (0-based)
     * Determines which period the current date falls into based on actual period dates
     */
    public function getCurrentPeriod()
    {
        if (!$this->is_active || !$this->started_at) {
            return 0;
        }

        // If periods exist, use them to determine current period
        if ($this->relationLoaded('periods') || $this->periods()->exists()) {
            $now = Carbon::now();
            $period = $this->periods()
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->first();
            
            if ($period) {
                return $period->period_number;
            }
            
            // If no current period found, find the first unpaid period that should be paid
            $firstUnpaid = $this->periods()
                ->where('is_paid', false)
                ->where('start_date', '<=', $now)
                ->orderBy('period_number')
                ->first();
            
            if ($firstUnpaid) {
                return $firstUnpaid->period_number;
            }
            
            // Fallback: return the first period
            return 0;
        }

        // Fallback to old calculation if periods don't exist
        $daysSinceStart = Carbon::now()->diffInDays($this->started_at, false);
        if ($daysSinceStart < 0) {
            return 0;
        }

        // Use average period length instead of fixed 30
        $totalPeriods = $this->getTotalPeriods();
        $avgDaysPerPeriod = $this->package_duration_days / $totalPeriods;
        
        return min((int) floor($daysSinceStart / $avgDaysPerPeriod), $totalPeriods - 1);
    }

    /**
     * Get total number of 30-day periods in this package
     * For packages that are multiples of months, return the number of months
     * Otherwise, use ceil to ensure all days are covered
     */
    public function getTotalPeriods()
    {
        // For packages that are close to month multiples (like 365 days = 12 months)
        // Return the number of months instead of using ceil
        $months = round($this->package_duration_days / 30.44); // Average days per month
        
        // If the package is close to a whole number of months (within 5 days)
        $expectedDays = $months * 30.44;
        if (abs($this->package_duration_days - $expectedDays) <= 5) {
            return (int) $months;
        }
        
        // Otherwise, use ceil to ensure all days are covered
        return (int) ceil($this->package_duration_days / 30);
    }

    /**
     * Check if a specific period is paid
     */
    public function isPeriodPaid($period)
    {
        if ($this->package_duration_days <= 30) {
            // For packages 30 days or less, check if fully paid
            return $this->payment_status === self::PAYMENT_STATUS_FULLY_PAID;
        }

        $paidPeriods = $this->paid_periods ?? [];
        return in_array($period, $paidPeriods);
    }

    /**
     * Mark a period as paid
     */
    public function markPeriodAsPaid($period)
    {
        $paidPeriods = $this->paid_periods ?? [];
        if (!in_array($period, $paidPeriods)) {
            $paidPeriods[] = $period;
            sort($paidPeriods);
            $this->paid_periods = $paidPeriods;
            $this->save();
        }
    }

    /**
     * Get amount for a specific period (30 days worth)
     */
    public function getPeriodAmount($period = null)
    {
        if ($period === null) {
            $period = $this->getCurrentPeriod();
        }

        // Calculate price per day
        $pricePerDay = $this->package_price / $this->package_duration_days;
        
        // Calculate amount for 30 days
        $periodDays = min(30, $this->package_duration_days - ($period * 30));
        return $pricePerDay * $periodDays;
    }

    /**
     * Get remaining unpaid periods
     */
    public function getUnpaidPeriods()
    {
        if ($this->package_duration_days <= 30) {
            return $this->payment_status !== self::PAYMENT_STATUS_FULLY_PAID ? [0] : [];
        }

        $totalPeriods = $this->getTotalPeriods();
        $paidPeriods = $this->paid_periods ?? [];
        $unpaidPeriods = [];

        for ($i = 0; $i < $totalPeriods; $i++) {
            if (!in_array($i, $paidPeriods)) {
                $unpaidPeriods[] = $i;
            }
        }

        return $unpaidPeriods;
    }

    /**
     * Generate periods for packages longer than 30 days
     */
    public function generatePeriods()
    {
        // Only generate periods for packages longer than 30 days
        if ($this->package_duration_days <= 30) {
            return;
        }

        // Delete existing periods if any
        $this->periods()->delete();

        $totalPeriods = $this->getTotalPeriods();
        $currentDate = $this->started_at ?? Carbon::now();
        
        // Calculate base days per period (integer division)
        $baseDaysPerPeriod = (int) floor($this->package_duration_days / $totalPeriods);
        $remainingDays = $this->package_duration_days % $totalPeriods;
        
        // Calculate total amount to distribute
        $totalAmount = round((float)$this->package_price, 0);
        
        // Calculate base amount per period (rounded down to nearest 1000)
        $baseAmountPerPeriod = (int) floor($totalAmount / $totalPeriods / 1000) * 1000;
        
        // Calculate remaining amount after distributing base amounts
        $remainingAmount = $totalAmount - ($baseAmountPerPeriod * $totalPeriods);
        
        $accumulatedDays = 0;
        
        for ($i = 0; $i < $totalPeriods; $i++) {
            // Distribute remaining days to the last period(s)
            $periodDays = $baseDaysPerPeriod;
            if ($i >= $totalPeriods - $remainingDays) {
                $periodDays += 1; // Add one extra day to the last periods
            }
            
            // Distribute remaining amount to the last period
            $periodAmount = $baseAmountPerPeriod;
            if ($i == $totalPeriods - 1) {
                $periodAmount += $remainingAmount; // Add all remaining amount to last period
            }
            
            $periodStartDate = $currentDate->copy()->addDays($accumulatedDays);
            $periodEndDate = $periodStartDate->copy()->addDays($periodDays)->subSecond();
            
            \App\Models\PackagePeriod::create([
                'organization_package_id' => $this->id,
                'period_number' => $i,
                'amount' => $periodAmount,
                'days' => $periodDays,
                'start_date' => $periodStartDate,
                'end_date' => $periodEndDate,
                'is_paid' => false,
                'paid_at' => null,
            ]);
            
            $accumulatedDays += $periodDays;
        }
    }

    /**
     * Get current unpaid period
     */
    public function getCurrentUnpaidPeriod()
    {
        if ($this->package_duration_days <= 30) {
            return null;
        }

        $currentPeriod = $this->getCurrentPeriod();
        $period = $this->periods()->where('period_number', $currentPeriod)->first();
        
        if ($period && !$period->is_paid) {
            return $period;
        }

        return null;
    }
}