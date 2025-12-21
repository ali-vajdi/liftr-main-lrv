<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\DB;

class BuildingContract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'building_id',
        'contract_start_date',
        'contract_end_date',
        'monthly_amount',
        'annual_amount',
        'payment_timing',
        'payment_frequency_type',
        'payment_frequency_value',
        'previous_debt',
        'status',
    ];

    protected $casts = [
        'contract_start_date' => 'timestamp',
        'contract_end_date' => 'timestamp',
        'monthly_amount' => 'decimal:2',
        'annual_amount' => 'decimal:2',
        'previous_debt' => 'decimal:2',
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

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Generate services for all months in the contract period
     */
    public function generateServices()
    {
        if (!$this->contract_start_date || !$this->contract_end_date) {
            return;
        }

        $startDate = Jalalian::forge($this->contract_start_date);
        $endDate = Jalalian::forge($this->contract_end_date);
        $currentDate = Jalalian::now();

        $startYear = $startDate->getYear();
        $startMonth = $startDate->getMonth();
        $endYear = $endDate->getYear();
        $endMonth = $endDate->getMonth();
        $currentYear = $currentDate->getYear();
        $currentMonth = $currentDate->getMonth();

        // Calculate total months in contract
        $totalMonths = (($endYear - $startYear) * 12) + ($endMonth - $startMonth) + 1;

        $year = $startYear;
        $month = $startMonth;

        for ($i = 0; $i < $totalMonths; $i++) {
            // Determine if service should be expired (if month/year is before current month/year)
            $isExpired = ($year < $currentYear) || ($year == $currentYear && $month < $currentMonth);
            
            // Check if service already exists for this month/year
            $existingService = Service::where('building_id', $this->building_id)
                ->where('building_contract_id', $this->id)
                ->where('service_year', $year)
                ->where('service_month', $month)
                ->first();

            if (!$existingService) {
                Service::create([
                    'building_id' => $this->building_id,
                    'building_contract_id' => $this->id,
                    'service_year' => $year,
                    'service_month' => $month,
                    'monthly_amount' => $this->monthly_amount,
                    'status' => $isExpired ? Service::STATUS_EXPIRED : Service::STATUS_PENDING,
                    'is_manual' => false,
                ]);
            }

            // Move to next month
            $month++;
            if ($month > 12) {
                $month = 1;
                $year++;
            }
        }
    }
}
