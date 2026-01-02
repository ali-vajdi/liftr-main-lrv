<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Morilog\Jalali\Jalalian;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'landline_phone',
        'logo',
        'status',
        'moderator_id',
        'sms_balance',
        'sms_cost_per_message',
        'contract_number_format',
        'contract_number_increment',
        'invoice_number_increment',
    ];

    protected $casts = [
        'status' => 'boolean',
        'sms_balance' => 'decimal:2',
        'sms_cost_per_message' => 'decimal:2',
        'contract_number_format' => 'array',
        'contract_number_increment' => 'integer',
        'invoice_number_increment' => 'integer',
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

    // Relationship with SMS
    public function sms()
    {
        return $this->hasMany(Sms::class);
    }

    /**
     * Generate simple increment contract number (starting from 1 for each organization)
     */
    public function generateContractNumber()
    {
        // Get max contract number for this organization through buildings
        $maxContractNumber = BuildingContract::whereHas('building', function($q) {
            $q->where('organization_id', $this->id);
        })->max('contract_number');
        
        // Return next number (starting from 1 if no contracts exist)
        // Convert to integer if it's a string
        $maxNumber = is_numeric($maxContractNumber) ? (int)$maxContractNumber : 0;
        return $maxNumber + 1;
    }

    /**
     * Generate formatted contract name based on organization settings
     */
    public function generateContractName()
    {
        // If no format is set, use simple increment as name
        if (!$this->contract_number_format || empty($this->contract_number_format['parts'])) {
            $this->contract_number_increment = ($this->contract_number_increment ?? 0) + 1;
            $this->save();
            return (string)$this->contract_number_increment;
        }

        $format = $this->contract_number_format;
        $parts = $format['parts'] ?? [];
        $separators = $format['separators'] ?? [];
        $customText = $format['custom_text'] ?? '';

        $resultParts = [];
        $jalaliDate = Jalalian::now();

        foreach ($parts as $index => $part) {
            switch ($part) {
                case 'increment':
                    $this->contract_number_increment = ($this->contract_number_increment ?? 0) + 1;
                    $this->save();
                    $resultParts[] = (string)$this->contract_number_increment;
                    break;
                case 'day':
                    $resultParts[] = (string)$jalaliDate->getDay();
                    break;
                case 'day_name':
                    $dayNames = [
                        0 => 'شنبه', 1 => 'یکشنبه', 2 => 'دوشنبه', 3 => 'سه‌شنبه',
                        4 => 'چهارشنبه', 5 => 'پنج‌شنبه', 6 => 'جمعه',
                    ];
                    // Convert Jalali date to Carbon to get day of week
                    // Jalali day of week is the same as Gregorian (Saturday = 0 in both)
                    $carbonDate = $jalaliDate->toCarbon();
                    $dayOfWeek = $carbonDate->dayOfWeek; // 0 = Saturday, 6 = Friday
                    $resultParts[] = $dayNames[$dayOfWeek] ?? '';
                    break;
                case 'month':
                    $monthNames = [
                        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
                        5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
                        9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
                    ];
                    $resultParts[] = $monthNames[$jalaliDate->getMonth()] ?? '';
                    break;
                case 'month_number':
                    $resultParts[] = (string)$jalaliDate->getMonth();
                    break;
                case 'year':
                    $resultParts[] = (string)$jalaliDate->getYear();
                    break;
                case 'text':
                    $resultParts[] = $customText;
                    break;
            }
        }

        // Join parts with separators
        $result = '';
        foreach ($resultParts as $index => $part) {
            if ($index > 0 && isset($separators[$index - 1])) {
                $result .= $separators[$index - 1];
            }
            $result .= $part;
        }

        return $result;
    }

    /**
     * Generate simple increment invoice number (starting from 1 for each organization)
     */
    public function generateInvoiceNumber()
    {
        $this->invoice_number_increment = ($this->invoice_number_increment ?? 0) + 1;
        $this->save();
        return (string)$this->invoice_number_increment;
    }
}
