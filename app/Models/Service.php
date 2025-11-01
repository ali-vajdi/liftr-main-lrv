<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'technician_id',
        'service_month',
        'service_year',
        'status',
        'notes',
        'assigned_at',
        'completed_at',
    ];

    protected $casts = [
        'service_month' => 'integer',
        'service_year' => 'integer',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_COMPLETED = 'completed';

    // Relationships
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'در انتظار',
            'assigned' => 'اختصاص داده شده',
            'completed' => 'تکمیل شده',
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'pending' => 'badge-warning',
            'assigned' => 'badge-info',
            'completed' => 'badge-success',
        ];
        
        return $classes[$this->status] ?? 'badge-secondary';
    }

    public function getServiceDateTextAttribute()
    {
        $months = [
            1 => 'فروردین',
            2 => 'اردیبهشت',
            3 => 'خرداد',
            4 => 'تیر',
            5 => 'مرداد',
            6 => 'شهریور',
            7 => 'مهر',
            8 => 'آبان',
            9 => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند',
        ];
        
        return ($months[$this->service_month] ?? $this->service_month) . ' ' . $this->service_year;
    }

    /**
     * Scope for pending services
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for assigned services
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', self::STATUS_ASSIGNED);
    }

    /**
     * Scope for completed services
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
