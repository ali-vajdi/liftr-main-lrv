<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transactionable_type',
        'transactionable_id',
        'payment_method_id',
        'amount',
        'type',
        'status',
        'reference_number',
        'description',
        'transaction_date',
        'organization_id',
        'moderator_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    // Transaction types
    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';

    // Transaction statuses
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    // Polymorphic relationship
    public function transactionable()
    {
        return $this->morphTo();
    }

    // Relationships
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function moderator()
    {
        return $this->belongsTo(Moderator::class);
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0) . ' تومان';
    }

    public function getTypeTextAttribute()
    {
        return match($this->type) {
            self::TYPE_INCOME => 'دریافت',
            self::TYPE_EXPENSE => 'پرداخت',
            default => 'نامشخص',
        };
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'در انتظار',
            self::STATUS_COMPLETED => 'تکمیل شده',
            self::STATUS_FAILED => 'ناموفق',
            self::STATUS_CANCELLED => 'لغو شده',
            default => 'نامشخص',
        };
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_FAILED => 'badge-danger',
            self::STATUS_CANCELLED => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    // Get transaction source type text
    public function getSourceTypeTextAttribute()
    {
        if ($this->transactionable_type === PackagePayment::class) {
            return 'پکیج';
        }
        return 'سایر';
    }
}
