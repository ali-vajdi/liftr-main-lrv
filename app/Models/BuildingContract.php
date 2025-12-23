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
                    // Create financial record for expired service
                    $this->createFinancialRecordForExpiredOrCancelledService($existingService, 'expired');
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
     * Get Persian month name
     */
    private function getMonthName($month)
    {
        $monthNames = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
            5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
            9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
        ];
        return $monthNames[$month] ?? $month;
    }

    /**
     * Create description from services (for periods)
     */
    private function createDescriptionFromServices($services)
    {
        $serviceDescriptions = $services->map(function ($s) {
            $monthName = $this->getMonthName($s->service_month);
            return "{$monthName} {$s->service_year}";
        })->unique()->sort()->values();

        if ($serviceDescriptions->isEmpty()) {
            return 'از بابت سرویس';
        }

        return 'از بابت سرویس ' . $serviceDescriptions->implode('، ');
    }

    /**
     * Create financial record for a payment period
     */
    public function createFinancialRecordForPeriod(PaymentPeriod $period)
    {
        // Get all services in period (excluding expired)
        $nonExpiredServices = $period->services()->where('status', '!=', Service::STATUS_EXPIRED)->get();
        
        // Separate completed and cancelled services
        $completedServices = $nonExpiredServices->filter(function ($s) {
            return $s->status === Service::STATUS_COMPLETED;
        });
        
        $cancelledServices = $nonExpiredServices->filter(function ($s) {
            return $s->status === Service::STATUS_CANCELLED;
        });

        // Create records for cancelled services with zero amount
        foreach ($cancelledServices as $cancelledService) {
            $monthName = $this->getMonthName($cancelledService->service_month);
            $description = "از بابت سرویس {$monthName}";
            $extraDescriptions = "از بابت کنسل کردن سرویس {$monthName}";

            $existingRecord = BuildingFinancialRecord::where('building_id', $this->building_id)
                ->where('building_contract_id', $this->id)
                ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
                ->where('description', $description)
                ->where('amount', 0)
                ->where('extra_descriptions', $extraDescriptions)
                ->first();

            if (!$existingRecord) {
                BuildingFinancialRecord::create([
                    'building_id' => $this->building_id,
                    'building_contract_id' => $this->id,
                    'type' => BuildingFinancialRecord::TYPE_DEBIT,
                    'amount' => 0,
                    'transaction_type' => BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT,
                    'description' => $description,
                    'extra_descriptions' => $extraDescriptions,
                    'transaction_date' => now(),
                ]);
            }
        }

        // Calculate amount from completed services only
        $amount = $completedServices->sum('monthly_amount');

        if ($amount <= 0) {
            return null; // No amount to record
        }

        // Create description from all services (completed + cancelled) for the period
        $allServicesForDescription = $nonExpiredServices->filter(function ($s) {
            return in_array($s->status, [Service::STATUS_COMPLETED, Service::STATUS_CANCELLED]);
        });
        
        $description = $this->createDescriptionFromServices($allServicesForDescription);

        // Check if financial record already exists for this period
        $existingRecord = BuildingFinancialRecord::where('building_id', $this->building_id)
            ->where('building_contract_id', $this->id)
            ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
            ->where('description', $description)
            ->where('amount', '>', 0)
            ->first();

        if ($existingRecord) {
            // Update existing record
            $existingRecord->update([
                'amount' => $amount,
            ]);
            return $existingRecord;
        }

        // Create new financial record
        return BuildingFinancialRecord::create([
            'building_id' => $this->building_id,
            'building_contract_id' => $this->id,
            'type' => BuildingFinancialRecord::TYPE_DEBIT,
            'amount' => $amount,
            'transaction_type' => BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT,
            'description' => $description,
            'transaction_date' => now(),
        ]);
    }

    /**
     * Create financial record for expired or cancelled service
     */
    private function createFinancialRecordForExpiredOrCancelledService(Service $service, $type = 'expired')
    {
        if (!$service->building_contract_id) {
            return; // Only for services with contracts
        }

        $monthName = $this->getMonthName($service->service_month);
        $description = "از بابت سرویس {$monthName}";
        
        $extraDescriptions = $type === 'expired' 
            ? 'از بابت مراجعه نکردن به سرویس و گذشت سررسید مراجعه'
            : "از بابت کنسل کردن سرویس {$monthName}";

        // Check if record already exists
        $existingRecord = BuildingFinancialRecord::where('building_id', $service->building_id)
            ->where('building_contract_id', $service->building_contract_id)
            ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
            ->where('description', $description)
            ->where('amount', 0)
            ->where('extra_descriptions', $extraDescriptions)
            ->first();

        if (!$existingRecord) {
            BuildingFinancialRecord::create([
                'building_id' => $service->building_id,
                'building_contract_id' => $service->building_contract_id,
                'type' => BuildingFinancialRecord::TYPE_DEBIT,
                'amount' => 0,
                'transaction_type' => BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT,
                'description' => $description,
                'extra_descriptions' => $extraDescriptions,
                'transaction_date' => now(),
            ]);
        }
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
            // Create description from services in this period
            $allServicesForDescription = $nonExpiredServices->filter(function ($s) {
                return in_array($s->status, [Service::STATUS_COMPLETED, Service::STATUS_CANCELLED, Service::STATUS_PENDING, Service::STATUS_ASSIGNED]);
            });
            
            $description = $this->createDescriptionFromServices($allServicesForDescription);
            
            $hasRecord = BuildingFinancialRecord::where('building_id', $this->building_id)
                ->where('building_contract_id', $this->id)
                ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
                ->where('description', $description)
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
            // Create description from services in this period
            $nonExpiredServices = $period->services->filter(function ($s) {
                return $s->status !== Service::STATUS_EXPIRED;
            });
            
            $allServicesForDescription = $nonExpiredServices->filter(function ($s) {
                return in_array($s->status, [Service::STATUS_COMPLETED, Service::STATUS_CANCELLED, Service::STATUS_PENDING, Service::STATUS_ASSIGNED]);
            });
            
            $description = $this->createDescriptionFromServices($allServicesForDescription);
            
            $hasRecord = BuildingFinancialRecord::where('building_id', $this->building_id)
                ->where('building_contract_id', $this->id)
                ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
                ->where('description', $description)
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
                // Create description from services in this period
                $nextNonExpiredServices = $period->services->filter(function ($s) {
                    return $s->status !== Service::STATUS_EXPIRED;
                });
                
                $allServicesForDescription = $nextNonExpiredServices->filter(function ($s) {
                    return in_array($s->status, [Service::STATUS_COMPLETED, Service::STATUS_CANCELLED, Service::STATUS_PENDING, Service::STATUS_ASSIGNED]);
                });
                
                $description = $this->createDescriptionFromServices($allServicesForDescription);
                
                $nextHasRecord = BuildingFinancialRecord::where('building_id', $this->building_id)
                    ->where('building_contract_id', $this->id)
                    ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
                    ->where('description', $description)
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
