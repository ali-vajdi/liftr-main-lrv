<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuildingFinancialRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'building_id',
        'building_contract_id',
        'type',
        'amount',
        'transaction_type',
        'description',
        'extra_descriptions',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    // Type constants
    const TYPE_DEBIT = 'debit';   // بدهکار (منفی)
    const TYPE_CREDIT = 'credit'; // بستانکار (مثبت)

    // Transaction type constants
    const TRANSACTION_SERVICE_PAYMENT = 'service_payment';
    const TRANSACTION_PREVIOUS_DEBT = 'previous_debt';
    const TRANSACTION_MANUAL_INCOME = 'manual_income';
    const TRANSACTION_MANUAL_PAYMENT = 'manual_payment';
    const TRANSACTION_CONTRACT_PAYMENT = 'contract_payment';

    // Relationships
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function buildingContract()
    {
        return $this->belongsTo(BuildingContract::class);
    }


    // Accessors
    public function getTypeTextAttribute()
    {
        return $this->type === self::TYPE_DEBIT ? 'بدهکار' : 'بستانکار';
    }

    public function getTransactionTypeTextAttribute()
    {
        $types = [
            self::TRANSACTION_SERVICE_PAYMENT => 'پرداخت بابت سرویس',
            self::TRANSACTION_PREVIOUS_DEBT => 'بدهی قبلی',
            self::TRANSACTION_MANUAL_INCOME => 'درآمد دستی',
            self::TRANSACTION_MANUAL_PAYMENT => 'پرداخت دستی',
            self::TRANSACTION_CONTRACT_PAYMENT => 'پرداخت بابت قرارداد',
        ];
        
        return $types[$this->transaction_type] ?? $this->transaction_type;
    }

    /**
     * Calculate building balance
     * All records are considered paid (no pending status)
     */
    public static function calculateBalance($buildingId)
    {
        $debits = self::where('building_id', $buildingId)
            ->where('type', self::TYPE_DEBIT)
            ->sum('amount');
        
        $credits = self::where('building_id', $buildingId)
            ->where('type', self::TYPE_CREDIT)
            ->sum('amount');
        
        return $credits - $debits; // Positive means building has credit (بستانکار), negative means building owes (بدهکار)
    }

    /**
     * Calculate pending amount
     * Since is_pending is removed, this always returns 0
     */
    public static function calculatePendingAmount($buildingId)
    {
        return 0;
    }
}
