<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackagePeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_package_id',
        'period_number',
        'amount',
        'days',
        'start_date',
        'end_date',
        'is_paid',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:0',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'paid_at' => 'datetime',
        'is_paid' => 'boolean',
    ];

    // Relationships
    public function organizationPackage()
    {
        return $this->belongsTo(OrganizationPackage::class);
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0) . ' تومان';
    }

    public function getStatusTextAttribute()
    {
        return $this->is_paid ? 'پرداخت شده' : 'پرداخت نشده';
    }

    public function getStatusBadgeClassAttribute()
    {
        return $this->is_paid ? 'badge-success' : 'badge-danger';
    }

    public function getIsExpiredAttribute()
    {
        return now()->gt($this->end_date);
    }

    public function getIsCurrentAttribute()
    {
        return now()->gte($this->start_date) && now()->lte($this->end_date);
    }
}
