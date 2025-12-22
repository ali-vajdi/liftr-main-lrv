<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentPeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'building_contract_id',
        'period_number',
        'amount',
        'payment_timing',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';

    // Relationships
    public function buildingContract()
    {
        return $this->belongsTo(BuildingContract::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'در انتظار پرداخت',
            'paid' => 'پرداخت شده',
            'overdue' => 'پس‌افتاده',
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'pending' => 'badge-warning',
            'paid' => 'badge-success',
            'overdue' => 'badge-danger',
        ];
        
        return $classes[$this->status] ?? 'badge-secondary';
    }

    /**
     * Calculate and update the period amount from linked services (excluding expired)
     */
    public function calculateAmount()
    {
        // Exclude expired services from amount calculation
        $this->amount = $this->services()
            ->where('status', '!=', Service::STATUS_EXPIRED)
            ->sum('monthly_amount');
        $this->save();
    }
}
