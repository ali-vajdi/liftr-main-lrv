<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentPeriod;
use App\Models\Service;
use App\Models\BuildingFinancialRecord;

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

    public function paymentPeriods()
    {
        return $this->hasMany(PaymentPeriod::class)->orderBy('period_number');
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
            } else {
                // Update existing service expiration status if needed
                if ($isExpired && $existingService->status !== Service::STATUS_EXPIRED) {
                    $existingService->update(['status' => Service::STATUS_EXPIRED]);
                }
            }

            // Move to next month
            $month++;
            if ($month > 12) {
                $month = 1;
                $year++;
            }
        }

        // Generate payment periods and link services
        $this->generatePaymentPeriods();
    }

    /**
     * Generate payment periods based on payment frequency value and link services
     * payment_frequency_value determines how many services per period
     */
    public function generatePaymentPeriods()
    {
        // Must have payment_frequency_value to generate periods
        if (!$this->payment_frequency_value) {
            return;
        }

        // Get all services for this contract, ordered by year and month
        $services = Service::where('building_contract_id', $this->id)
            ->orderBy('service_year', 'asc')
            ->orderBy('service_month', 'asc')
            ->get();

        if ($services->isEmpty()) {
            return;
        }

        // Clear existing payment period links for this contract's services
        Service::where('building_contract_id', $this->id)
            ->update(['payment_period_id' => null]);

        $frequencyValue = $this->payment_frequency_value;
        $periodNumber = 1;
        $serviceIndex = 0;
        $totalServices = $services->count();

        // Group services into periods based on frequency value
        // Last period can have fewer services if there aren't enough left
        while ($serviceIndex < $totalServices) {
            // Get services for this period (up to frequencyValue services, or remaining if less)
            $remainingServices = $totalServices - $serviceIndex;
            $servicesForThisPeriod = min($frequencyValue, $remainingServices);
            $periodServices = $services->slice($serviceIndex, $servicesForThisPeriod);

            // Create or get payment period
            $paymentPeriod = PaymentPeriod::firstOrCreate(
                [
                    'building_contract_id' => $this->id,
                    'period_number' => $periodNumber,
                ],
                [
                    'payment_timing' => $this->payment_timing,
                    'status' => PaymentPeriod::STATUS_PENDING,
                    'amount' => 0,
                ]
            );

            // Update payment_timing if it changed
            if ($paymentPeriod->payment_timing !== $this->payment_timing) {
                $paymentPeriod->payment_timing = $this->payment_timing;
                $paymentPeriod->save();
            }

            // Link services to this period
            foreach ($periodServices as $service) {
                $service->payment_period_id = $paymentPeriod->id;
                $service->save();
            }

            // Calculate and update period amount (excluding expired services)
            $paymentPeriod->calculateAmount();

            $serviceIndex += $servicesForThisPeriod;
            $periodNumber++;
        }
        
        // For before_service: Create financial record for the FIRST ACTIVE period (skip expired periods)
        // Other periods will be created one by one as previous periods finish
        if ($this->payment_timing === 'before_service') {
            $this->createFirstActivePeriodRecord();
        }

        // Remove any extra periods that don't have services
        PaymentPeriod::where('building_contract_id', $this->id)
            ->where('period_number', '>=', $periodNumber)
            ->delete();
    }

    /**
     * Create financial record for a payment period
     */
    public function createFinancialRecordForPeriod(PaymentPeriod $period)
    {
        // Check if financial record already exists for this period
        $existingRecord = BuildingFinancialRecord::where('building_id', $this->building_id)
            ->where('building_contract_id', $this->id)
            ->where('payment_period_id', $period->id)
            ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
            ->first();

        // Calculate amount excluding expired services
        $nonExpiredServices = $period->services()->where('status', '!=', Service::STATUS_EXPIRED)->get();
        $amount = $nonExpiredServices->sum('monthly_amount');

        if ($amount <= 0) {
            return null; // No amount to record
        }

        // Get first non-expired service in period for date reference
        $firstService = $nonExpiredServices->sortBy(function ($s) {
            return $s->service_year * 12 + $s->service_month;
        })->first();

        if ($existingRecord) {
            // Update existing record
            $existingRecord->update([
                'amount' => $amount,
                'is_pending' => $period->payment_timing === 'before_service' ? true : false,
                'service_id' => $firstService ? $firstService->id : null,
                'service_month' => $firstService ? $firstService->service_month : null,
                'service_year' => $firstService ? $firstService->service_year : null,
            ]);
            return $existingRecord;
        }

        // Create new financial record
        $description = $period->payment_timing === 'before_service' 
            ? "پرداخت بابت دوره {$period->period_number} - قبل از انجام سرویس"
            : "پرداخت بابت دوره {$period->period_number} - بعد از انجام سرویس";

        return BuildingFinancialRecord::create([
            'building_id' => $this->building_id,
            'building_contract_id' => $this->id,
            'payment_period_id' => $period->id,
            'service_id' => $firstService ? $firstService->id : null,
            'type' => BuildingFinancialRecord::TYPE_DEBIT,
            'amount' => $amount,
            'transaction_type' => BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT,
            'description' => $description,
            'service_month' => $firstService ? $firstService->service_month : null,
            'service_year' => $firstService ? $firstService->service_year : null,
            'is_pending' => $period->payment_timing === 'before_service' ? true : false,
            'transaction_date' => now(),
        ]);
    }

    /**
     * Create financial record for the first active period (skip expired periods)
     */
    private function createFirstActivePeriodRecord()
    {
        $allPeriods = $this->paymentPeriods()->with('services')->orderBy('period_number')->get();

        // Find the first active period (has non-expired services)
        foreach ($allPeriods as $period) {
            // Reload services to ensure fresh data
            $period->load('services');
            
            $nonExpiredServices = $period->services->filter(function ($s) {
                return $s->status !== Service::STATUS_EXPIRED;
            });
            
            // Skip if period has no non-expired services (fully expired)
            if ($nonExpiredServices->isEmpty()) {
                continue;
            }

            // Check if this period already has a financial record
            $hasRecord = BuildingFinancialRecord::where('building_id', $this->building_id)
                ->where('building_contract_id', $this->id)
                ->where('payment_period_id', $period->id)
                ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
                ->exists();
            
            if ($hasRecord) {
                return; // Already has a record, stop here
            }

            // Check if any service in this period has been completed
            $hasCompletedServices = $nonExpiredServices->contains(function ($s) {
                return $s->status === Service::STATUS_COMPLETED;
            });
            
            // Create record for the first active period that hasn't started yet
            if (!$hasCompletedServices && $period->amount > 0) {
                $this->createFinancialRecordForPeriod($period);
                return; // Only create for the first active period
            }
        }
    }

    /**
     * Sync financial records for before_service periods
     * Creates records one by one: when a period finishes, only the next active period is created
     */
    public function syncBeforeServiceFinancialRecords()
    {
        if ($this->payment_timing !== 'before_service') {
            return;
        }

        // Reload periods with services to ensure fresh data
        $allPeriods = $this->paymentPeriods()->with('services')->orderBy('period_number')->get();

        // Find the last period that has a financial record
        $lastPeriodWithRecord = null;
        foreach ($allPeriods as $period) {
            $hasRecord = BuildingFinancialRecord::where('building_id', $this->building_id)
                ->where('building_contract_id', $this->id)
                ->where('payment_period_id', $period->id)
                ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
                ->exists();
            
            if ($hasRecord) {
                $lastPeriodWithRecord = $period;
            }
        }

        // If no period has a record yet, create one for the first active period
        if (!$lastPeriodWithRecord) {
            $this->createFirstActivePeriodRecord();
            return;
        }

        // Reload services for the last period with record to ensure fresh data
        $lastPeriodWithRecord->load('services');
        
        // Check if the last period with a record is finished
        $nonExpiredServices = $lastPeriodWithRecord->services->filter(function ($s) {
            return $s->status !== Service::STATUS_EXPIRED;
        });
        
        if ($nonExpiredServices->isEmpty()) {
            return; // Period is fully expired
        }

        $allFinished = $nonExpiredServices->every(function ($s) {
            return in_array($s->status, [Service::STATUS_COMPLETED, Service::STATUS_CANCELLED]);
        });

        // If the last period with a record is finished, find and create the next active period
        if ($allFinished) {
            $nextPeriodNumber = $lastPeriodWithRecord->period_number + 1;
            
            // Find the next active period (skip expired periods)
            foreach ($allPeriods as $period) {
                // Skip periods before or equal to the finished period
                if ($period->period_number <= $lastPeriodWithRecord->period_number) {
                    continue;
                }

                // Reload services for this period
                $period->load('services');
                
                $nextNonExpiredServices = $period->services->filter(function ($s) {
                    return $s->status !== Service::STATUS_EXPIRED;
                });
                
                // Skip if period is fully expired
                if ($nextNonExpiredServices->isEmpty()) {
                    continue;
                }

                // Check if this period already has a record
                $nextHasRecord = BuildingFinancialRecord::where('building_id', $this->building_id)
                    ->where('building_contract_id', $this->id)
                    ->where('payment_period_id', $period->id)
                    ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
                    ->exists();
                
                if ($nextHasRecord) {
                    return; // Already has a record
                }

                // Check if any service in this period has been completed
                $nextHasCompletedServices = $nextNonExpiredServices->contains(function ($s) {
                    return $s->status === Service::STATUS_COMPLETED;
                });
                
                // Create record for the next active period that hasn't started yet
                if (!$nextHasCompletedServices && $period->amount > 0) {
                    $this->createFinancialRecordForPeriod($period);
                    return; // Only create one period at a time
                }
            }
        }
    }
}
