<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'building_id',
        'building_contract_id',
        'payment_period_id',
        'technician_id',
        'service_month',
        'service_year',
        'status',
        'is_manual',
        'monthly_amount',
        'notes',
        'organization_note',
        'user_note',
        'technician_note',
        'assigned_at',
        'completed_at',
        'visit_date',
        'visit_time_range',
        'slug',
    ];

    protected $casts = [
        'service_month' => 'integer',
        'service_year' => 'integer',
        'is_manual' => 'boolean',
        'monthly_amount' => 'decimal:2',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'visit_date' => 'date',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_COMPLETED = 'completed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function buildingContract()
    {
        return $this->belongsTo(BuildingContract::class);
    }

    public function paymentPeriod()
    {
        return $this->belongsTo(PaymentPeriod::class);
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }

    public function checklist()
    {
        return $this->hasOne(ServiceChecklist::class);
    }

    public function views()
    {
        return $this->hasMany(ServiceView::class);
    }

    public function pdfVerificationCodes()
    {
        return $this->hasMany(PdfVerificationCode::class);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'در انتظار',
            'assigned' => 'اختصاص داده شده',
            'completed' => 'انجام شده',
            'expired' => 'منقضی شده',
            'cancelled' => 'لغو شده',
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'pending' => 'badge-warning',
            'assigned' => 'badge-info',
            'completed' => 'badge-success',
            'expired' => 'badge-danger',
            'cancelled' => 'badge-secondary',
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

    /**
     * Scope for expired services
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    /**
     * Scope for cancelled services
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Generate a unique 3-character slug
     */
    protected static function generateUniqueSlug(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $maxAttempts = 100;
        $attempt = 0;

        do {
            $slug = '';
            for ($i = 0; $i < 5; $i++) {
                $slug .= $characters[random_int(0, strlen($characters) - 1)];
            }
            $attempt++;
        } while (static::withTrashed()->where('slug', $slug)->exists() && $attempt < $maxAttempts);

        if ($attempt >= $maxAttempts) {
            throw new \Exception('Unable to generate unique slug after ' . $maxAttempts . ' attempts');
        }

        return $slug;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = static::generateUniqueSlug();
            }
        });
    }
}
