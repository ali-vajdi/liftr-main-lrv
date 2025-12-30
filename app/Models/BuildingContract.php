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
        'contract_number',
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

        // Calculate total months in contract (excluding the end month)
        // For example: 1404/01/01 to 1405/01/01 should create 12 services (فروردین 1404 to اسفند 1404)
        $totalMonths = (($endYear - $startYear) * 12) + ($endMonth - $startMonth);

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

            // Skip creating expired services and their financial records
            if ($isExpired) {
                // Skip expired services - do not create service or financial record
                // Move to next month
                $month++;
                if ($month > 12) {
                    $month = 1;
                    $year++;
                }
                continue;
            }

            if (!$existingService) {
                $service = Service::create([
                    'building_id' => $this->building_id,
                    'building_contract_id' => $this->id,
                    'service_year' => $year,
                    'service_month' => $month,
                    'monthly_amount' => $this->monthly_amount,
                    'status' => Service::STATUS_PENDING,
                    'is_manual' => false,
                ]);
                
                // Create financial record for expired service
                // COMMENTED OUT: Should not create financial records for expired services
                // if ($isExpired) {
                //     $this->createFinancialRecordForExpiredService($service);
                // }
            } else {
                // Update existing service expiration status if needed
                // COMMENTED OUT: Should not update to expired or create financial records for expired services
                // if ($isExpired && $existingService->status !== Service::STATUS_EXPIRED) {
                //     $existingService->update(['status' => Service::STATUS_EXPIRED]);
                //     // Create financial record for newly expired service
                //     $this->createFinancialRecordForExpiredService($existingService);
                // }
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
     * Create financial record for an expired service
     */
    private function createFinancialRecordForExpiredService(Service $service)
    {
        if (!$service->building_contract_id) {
            return; // Only for services with contracts
        }

        $monthName = $this->getMonthName($service->service_month);
        $description = "از بابت سرویس {$monthName} {$service->service_year}";
        $extraDescriptions = 'از بابت مراجعه نکردن به سرویس و گذشت سررسید مراجعه';

        // Check if record already exists
        $existingRecord = BuildingFinancialRecord::where('building_id', $service->building_id)
            ->where('building_contract_id', $service->building_contract_id)
            ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
            ->where('description', $description)
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
     * Create financial records for all services in a period
     * Creates one record per service with description listing all service months
     */
    public function createFinancialRecordsForPeriod(PaymentPeriod $period)
    {
        $services = $period->services()->orderBy('service_year')->orderBy('service_month')->get();
        
        if ($services->isEmpty()) {
            return;
        }

        // Create description with all service months
        $monthNames = $services->map(function ($s) {
            return $this->getMonthName($s->service_month) . ' ' . $s->service_year;
        })->unique()->sort()->values();
        
        $description = 'از بابت سرویس ' . $monthNames->implode('، ');

        // Check if period record already exists
        $existingRecord = BuildingFinancialRecord::where('building_id', $this->building_id)
            ->where('building_contract_id', $this->id)
            ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
            ->where('description', $description)
            ->first();

        // Calculate amount from completed services only
        $completedServices = $services->filter(function ($s) {
            return $s->status === Service::STATUS_COMPLETED;
        });
        $amount = $completedServices->sum('monthly_amount');

        if ($existingRecord) {
            $existingRecord->update(['amount' => $amount]);
        } else if ($amount > 0) {
            BuildingFinancialRecord::create([
                'building_id' => $this->building_id,
                'building_contract_id' => $this->id,
                'type' => BuildingFinancialRecord::TYPE_DEBIT,
                'amount' => $amount,
                'transaction_type' => BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT,
                'description' => $description,
                'transaction_date' => now(),
            ]);
        }

        // Create records for cancelled services in this period (zero amount)
        foreach ($services as $service) {
            if ($service->status === Service::STATUS_CANCELLED) {
                $monthName = $this->getMonthName($service->service_month);
                $serviceDescription = "از بابت سرویس {$monthName} {$service->service_year}";
                $extraDescriptions = "از بابت کنسل کردن سرویس {$monthName} {$service->service_year}";

                $existingCancelledRecord = BuildingFinancialRecord::where('building_id', $this->building_id)
                    ->where('building_contract_id', $this->id)
                    ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
                    ->where('description', $serviceDescription)
                    ->where('extra_descriptions', $extraDescriptions)
                    ->first();

                if (!$existingCancelledRecord) {
                    BuildingFinancialRecord::create([
                        'building_id' => $this->building_id,
                        'building_contract_id' => $this->id,
                        'type' => BuildingFinancialRecord::TYPE_DEBIT,
                        'amount' => 0,
                        'transaction_type' => BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT,
                        'description' => $serviceDescription,
                        'extra_descriptions' => $extraDescriptions,
                        'transaction_date' => now(),
                    ]);
                }
            }
        }
    }

}
