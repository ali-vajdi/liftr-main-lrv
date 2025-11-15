<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackagePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_package_id',
        'payment_method_id',
        'amount',
        'payment_date',
        'notes',
        'moderator_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    // Relationships
    public function organizationPackage()
    {
        return $this->belongsTo(OrganizationPackage::class);
    }

    public function moderator()
    {
        return $this->belongsTo(Moderator::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'transactionable');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0) . ' تومان';
    }
}
