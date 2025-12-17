<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildingContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'contract_start_date',
        'contract_end_date',
        'monthly_amount',
        'annual_amount',
        'payment_timing',
        'payment_frequency_type',
        'payment_frequency_value',
        'is_custom_payment_method',
        'previous_debt',
        'status',
    ];

    protected $casts = [
        'contract_start_date' => 'timestamp',
        'contract_end_date' => 'timestamp',
        'monthly_amount' => 'decimal:2',
        'annual_amount' => 'decimal:2',
        'previous_debt' => 'decimal:2',
        'is_custom_payment_method' => 'boolean',
        'payment_frequency_value' => 'integer',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_FINISHED = 'finished';
    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
