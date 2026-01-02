<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\BuildingContract;
use App\Models\BuildingFinancialRecord;
use App\Models\Organization;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class FinancialDashboardController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }
    /**
     * Get financial dashboard data - financial records for a specific building
     */
    public function index(Request $request, $building)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify building belongs to the organization - support both ID and slug
        $buildingQuery = \App\Models\Building::where('organization_id', $user->organization_id);
        
        // Check if $building is numeric (ID) or string (slug)
        if (is_numeric($building)) {
            $building = $buildingQuery->findOrFail($building);
        } else {
            $building = $buildingQuery->where('slug', $building)->firstOrFail();
        }

        // Get all financial records for this building, ordered by ID ascending for balance calculation
        $recordsForBalance = BuildingFinancialRecord::where('building_id', $building->id)
            ->orderBy('id', 'ASC')
            ->get();

        // Calculate cumulative balance (from oldest to newest)
        $cumulativeBalance = 0;
        $balanceMap = [];
        foreach ($recordsForBalance as $record) {
            // Calculate balance: debit (بدهکاری) is negative, credit (بستانکاری) is positive
            if ($record->type === BuildingFinancialRecord::TYPE_DEBIT) {
                $cumulativeBalance -= $record->amount;
            } else {
                $cumulativeBalance += $record->amount;
            }
            $balanceMap[$record->id] = $cumulativeBalance;
        }

        // Get all records ordered by ID ascending (oldest first) for display
        $records = BuildingFinancialRecord::where('building_id', $building->id)
            ->orderBy('id', 'ASC')
            ->get();

        // Format records with balance from map
        $formattedRecords = $records->map(function ($record) use ($balanceMap) {
            // Get transaction date (prefer transaction_date, fallback to created_at)
            $transactionDate = $record->transaction_date ?? $record->created_at;

            return [
                'id' => $record->id,
                'building_contract_id' => $record->building_contract_id,
                'transaction_date' => $transactionDate ? $transactionDate->toIso8601String() : null,
                'transaction_date_jalali' => $transactionDate ? Jalalian::forge($transactionDate)->format('Y/m/d') : null,
                'description' => $record->description,
                'debit' => $record->type === BuildingFinancialRecord::TYPE_DEBIT ? $record->amount : null,
                'credit' => $record->type === BuildingFinancialRecord::TYPE_CREDIT ? $record->amount : null,
                'balance' => $balanceMap[$record->id] ?? 0,
                'extra_descriptions' => $record->extra_descriptions,
                'type' => $record->type,
                'amount' => $record->amount,
                'transaction_type' => $record->transaction_type,
            ];
        });

        // Calculate balance (for summary)
        $balance = BuildingFinancialRecord::calculateBalance($building->id);
        $pendingAmount = BuildingFinancialRecord::calculatePendingAmount($building->id);

        // Calculate totals
        $totalDebits = $records->where('type', BuildingFinancialRecord::TYPE_DEBIT)->sum('amount');
        $totalCredits = $records->where('type', BuildingFinancialRecord::TYPE_CREDIT)->sum('amount');

        // Get active contract, or last contract if no active contract exists
        $contract = BuildingContract::where('building_id', $building->id)
            ->where('status', BuildingContract::STATUS_ACTIVE)
            ->orderBy('created_at', 'desc')
            ->first();

        // If no active contract, get the last contract (by created_at)
        if (!$contract) {
            $contract = BuildingContract::where('building_id', $building->id)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        // Format contract data if exists
        $contractData = null;
        if ($contract) {
            // Handle dates - timestamp cast returns Carbon instance in Laravel
            $startDate = $contract->contract_start_date;
            $endDate = $contract->contract_end_date;
            
            // Ensure we have Carbon instances (timestamp cast should already provide this)
            if ($startDate && !($startDate instanceof Carbon)) {
                $startDate = Carbon::createFromTimestamp($startDate);
            }
            if ($endDate && !($endDate instanceof Carbon)) {
                $endDate = Carbon::createFromTimestamp($endDate);
            }
            
            $contractData = [
                'id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'contract_name' => $contract->contract_name,
                'status' => $contract->status,
                'status_text' => $contract->status === BuildingContract::STATUS_ACTIVE ? 'فعال' : 
                                ($contract->status === BuildingContract::STATUS_FINISHED ? 'تمام شده' : 'لغو شده'),
                'contract_start_date' => $startDate ? $startDate->toIso8601String() : null,
                'contract_start_date_jalali' => $startDate ? Jalalian::forge($startDate)->format('Y/m/d') : null,
                'contract_end_date' => $endDate ? $endDate->toIso8601String() : null,
                'contract_end_date_jalali' => $endDate ? Jalalian::forge($endDate)->format('Y/m/d') : null,
                'monthly_amount' => $contract->monthly_amount,
                'annual_amount' => $contract->annual_amount,
                'previous_debt' => $contract->previous_debt,
                'payment_timing' => $contract->payment_timing,
                'payment_frequency_value' => $contract->payment_frequency_value,
                'is_active' => $contract->status === BuildingContract::STATUS_ACTIVE,
            ];

            // Map payment method
            $contractData['payment_method'] = $this->mapPaymentMethod($contract);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'building' => [
                    'id' => $building->id,
                    'name' => $building->name,
                    'manager_name' => $building->manager_name,
                    'manager_phone' => $building->manager_phone,
                    'address' => $building->address,
                ],
                'contract' => $contractData,
                'balance' => $balance,
                'pending_amount' => $pendingAmount,
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits,
                'records' => $formattedRecords,
                'summary' => [
                    'total_records' => $records->count(),
                    'balance' => $balance,
                    'pending_amount' => $pendingAmount,
                    'total_debits' => $totalDebits,
                    'total_credits' => $totalCredits,
                ]
            ]
        ]);
    }

    /**
     * Add manual financial transaction (income or payment)
     */
    public function addTransaction(Request $request, $building)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify building belongs to the organization
        $building = \App\Models\Building::where('organization_id', $user->organization_id)
            ->findOrFail($building);

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|in:manual_income,manual_payment',
            'description' => 'required|string|max:1000',
            'extra_descriptions' => 'nullable|string|max:2000',
            'transaction_date' => 'required|string',
        ], [
            'type.required' => 'نوع تراکنش الزامی است',
            'type.in' => 'نوع تراکنش نامعتبر است',
            'amount.required' => 'مبلغ الزامی است',
            'amount.numeric' => 'مبلغ باید عدد باشد',
            'amount.min' => 'مبلغ باید بیشتر از 0 باشد',
            'transaction_type.required' => 'نوع تراکنش مالی الزامی است',
            'transaction_type.in' => 'نوع تراکنش مالی نامعتبر است',
            'description.required' => 'شرح الزامی است',
            'transaction_date.required' => 'تاریخ الزامی است',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Convert Jalali date to Gregorian
        $transactionDate = now();
        if ($request->transaction_date) {
            try {
                // Try to parse as Jalali date (Y/m/d format)
                $jalaliDate = Jalalian::fromFormat('Y/m/d', $request->transaction_date);
                $transactionDate = $jalaliDate->toCarbon();
            } catch (\Exception $e) {
                // If Jalali parsing fails, try to parse as Gregorian date
                try {
                    $transactionDate = Carbon::parse($request->transaction_date);
                } catch (\Exception $e2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'فرمت تاریخ نامعتبر است',
                        'errors' => ['transaction_date' => ['فرمت تاریخ نامعتبر است']]
                    ], 422);
                }
            }
        }

        $record = BuildingFinancialRecord::create([
            'building_id' => $building->id,
            'type' => $request->type,
            'amount' => $request->amount,
            'transaction_type' => $request->transaction_type,
            'description' => $request->description ?? ($request->type === 'credit' ? 'دریافت وجه از ساختمان' : 'پرداخت به ساختمان'),
            'extra_descriptions' => $request->extra_descriptions,
            'transaction_date' => $transactionDate,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تراکنش مالی با موفقیت ثبت شد',
            'data' => $record
        ], 201);
    }

    /**
     * Update financial transaction
     */
    public function updateTransaction(Request $request, $building, $record)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify building belongs to the organization
        $building = \App\Models\Building::where('organization_id', $user->organization_id)
            ->findOrFail($building);

        // Get the financial record
        $financialRecord = BuildingFinancialRecord::where('building_id', $building->id)
            ->findOrFail($record);

        // Only allow editing records without building_contract_id
        if ($financialRecord->building_contract_id) {
            return response()->json([
                'success' => false,
                'message' => 'این تراکنش قابل ویرایش نیست'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|in:manual_income,manual_payment',
            'description' => 'required|string|max:1000',
            'extra_descriptions' => 'nullable|string|max:2000',
            'transaction_date' => 'required|string',
        ], [
            'type.required' => 'نوع تراکنش الزامی است',
            'type.in' => 'نوع تراکنش نامعتبر است',
            'amount.required' => 'مبلغ الزامی است',
            'amount.numeric' => 'مبلغ باید عدد باشد',
            'amount.min' => 'مبلغ باید بیشتر از 0 باشد',
            'transaction_type.required' => 'نوع تراکنش مالی الزامی است',
            'transaction_type.in' => 'نوع تراکنش مالی نامعتبر است',
            'description.required' => 'شرح الزامی است',
            'transaction_date.required' => 'تاریخ الزامی است',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Convert Jalali date to Gregorian
        $transactionDate = now();
        if ($request->transaction_date) {
            try {
                // Try to parse as Jalali date (Y/m/d format)
                $jalaliDate = Jalalian::fromFormat('Y/m/d', $request->transaction_date);
                $transactionDate = $jalaliDate->toCarbon();
            } catch (\Exception $e) {
                // If Jalali parsing fails, try to parse as Gregorian date
                try {
                    $transactionDate = Carbon::parse($request->transaction_date);
                } catch (\Exception $e2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'فرمت تاریخ نامعتبر است',
                        'errors' => ['transaction_date' => ['فرمت تاریخ نامعتبر است']]
                    ], 422);
                }
            }
        }

        $financialRecord->update([
            'type' => $request->type,
            'amount' => $request->amount,
            'transaction_type' => $request->transaction_type,
            'description' => $request->description ?? ($request->type === 'credit' ? 'دریافت وجه از ساختمان' : 'پرداخت به ساختمان'),
            'extra_descriptions' => $request->extra_descriptions,
            'transaction_date' => $transactionDate,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تراکنش مالی با موفقیت به‌روزرسانی شد',
            'data' => $financialRecord
        ]);
    }

    /**
     * Delete financial transaction
     */
    public function deleteTransaction(Request $request, $building, $record)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify building belongs to the organization
        $building = \App\Models\Building::where('organization_id', $user->organization_id)
            ->findOrFail($building);

        // Get the financial record
        $financialRecord = BuildingFinancialRecord::where('building_id', $building->id)
            ->findOrFail($record);

        // Only allow deleting records without building_contract_id
        if ($financialRecord->building_contract_id) {
            return response()->json([
                'success' => false,
                'message' => 'این تراکنش قابل حذف نیست'
            ], 403);
        }

        $financialRecord->delete();

        return response()->json([
            'success' => true,
            'message' => 'تراکنش مالی با موفقیت حذف شد'
        ]);
    }

    /**
     * Get all buildings with their financial summary
     */
    public function getAllBuildingsFinancialSummary(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Get all buildings for this organization
        $buildings = \App\Models\Building::where('organization_id', $user->organization_id)
            ->orderBy('name')
            ->get();

        $buildingsData = $buildings->map(function ($building) {
            // Calculate financial summary for each building
            $totalDebits = BuildingFinancialRecord::where('building_id', $building->id)
                ->where('type', BuildingFinancialRecord::TYPE_DEBIT)
                ->sum('amount');

            $totalCredits = BuildingFinancialRecord::where('building_id', $building->id)
                ->where('type', BuildingFinancialRecord::TYPE_CREDIT)
                ->sum('amount');

            $balance = BuildingFinancialRecord::calculateBalance($building->id);

            // Get building status (active contract or not)
            $activeContract = BuildingContract::where('building_id', $building->id)
                ->where('status', BuildingContract::STATUS_ACTIVE)
                ->first();

            $status = $activeContract ? 'فعال' : 'غیرفعال';

            return [
                'id' => $building->id,
                'slug' => $building->slug,
                'name' => $building->name,
                'manager_name' => $building->manager_name,
                'manager_phone' => $building->manager_phone,
                'status' => $status,
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits,
                'balance' => $balance,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $buildingsData
        ]);
    }

    /**
     * Get service date text
     */
    private function getServiceDateText($month, $year)
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
        
        return ($months[$month] ?? $month) . ' ' . $year;
    }

    /**
     * Export financial dashboard as PDF
     */
    public function exportPdf(Request $request, $building)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // Load organization
        $user->load('organization');
        $organization = $user->organization;

        // Verify building belongs to the organization - support both ID and slug
        $buildingQuery = \App\Models\Building::where('organization_id', $user->organization_id);
        
        // Check if $building is numeric (ID) or string (slug)
        if (is_numeric($building)) {
            $building = $buildingQuery->find($building);
        } else {
            $building = $buildingQuery->where('slug', $building)->first();
        }
        
        if (!$building) {
            abort(404, 'ساختمان یافت نشد');
        }

        // Get all financial records for this building, ordered by ID ascending for balance calculation
        $recordsForBalance = BuildingFinancialRecord::where('building_id', $building->id)
            ->orderBy('id', 'ASC')
            ->get();

        // Calculate cumulative balance (from oldest to newest)
        $cumulativeBalance = 0;
        $balanceMap = [];
        foreach ($recordsForBalance as $record) {
            // Calculate balance: debit (بدهکاری) is negative, credit (بستانکاری) is positive
            if ($record->type === BuildingFinancialRecord::TYPE_DEBIT) {
                $cumulativeBalance -= $record->amount;
            } else {
                $cumulativeBalance += $record->amount;
            }
            $balanceMap[$record->id] = $cumulativeBalance;
        }

        // Get all records ordered by ID ascending (oldest first) for display
        $records = BuildingFinancialRecord::where('building_id', $building->id)
            ->orderBy('id', 'ASC')
            ->get();

        // Format records with balance from map
        $formattedRecords = $records->map(function ($record) use ($balanceMap) {
            // Get transaction date (prefer transaction_date, fallback to created_at)
            $transactionDate = $record->transaction_date ?? $record->created_at;

            return [
                'id' => $record->id,
                'transaction_date' => $transactionDate,
                'transaction_date_jalali' => $transactionDate ? Jalalian::forge($transactionDate)->format('Y/m/d') : null,
                'description' => $record->description,
                'debit' => $record->type === BuildingFinancialRecord::TYPE_DEBIT ? $record->amount : null,
                'credit' => $record->type === BuildingFinancialRecord::TYPE_CREDIT ? $record->amount : null,
                'balance' => $balanceMap[$record->id] ?? 0,
            ];
        });

        // Calculate totals
        $totalDebits = $records->where('type', BuildingFinancialRecord::TYPE_DEBIT)->sum('amount');
        $totalCredits = $records->where('type', BuildingFinancialRecord::TYPE_CREDIT)->sum('amount');
        $finalBalance = BuildingFinancialRecord::calculateBalance($building->id);

        // Get current Jalali date
        $currentDate = Jalalian::now()->format('Y/m/d');

        // Convert final balance to Persian words (use absolute value for display)
        $finalBalanceInWords = $this->numberToPersianWords(abs($finalBalance));

        // Split records into pages (approximately 20 records per page after header/building info)
        $recordsPerPage = 15;
        $recordChunks = $formattedRecords->chunk($recordsPerPage);

        // Generate PDF
        $pdf = PDF::loadView('organization.financial-dashboard.pdf', [
            'organization' => $organization,
            'building' => $building,
            'recordChunks' => $recordChunks,
            'totalDebits' => $totalDebits,
            'totalCredits' => $totalCredits,
            'finalBalance' => $finalBalance,
            'finalBalanceInWords' => $finalBalanceInWords,
            'currentDate' => $currentDate,
        ]);

        $filename = 'صورتحساب_مالی_' . $building->name . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Map database fields to payment method selection
     * Determines if contract matches a predefined option or is custom
     */
    private function mapPaymentMethod($contract)
    {
        // Determine which predefined option matches based on field values
        if ($contract->payment_timing === 'after_service' && $contract->payment_frequency_value == 1) {
            return '1';
        } elseif ($contract->payment_timing === 'after_service' && $contract->payment_frequency_value == 2) {
            return '2';
        } elseif ($contract->payment_timing === 'after_service' && $contract->payment_frequency_value == 3) {
            return '3';
        } elseif ($contract->payment_timing === 'before_service' && $contract->payment_frequency_value == 3) {
            return '4';
        } elseif ($contract->payment_timing === 'before_service' && $contract->payment_frequency_value == 6) {
            return '5';
        } elseif ($contract->payment_timing === 'before_service' && $contract->payment_frequency_value == 12) {
            return '6';
        } else {
            // Doesn't match any predefined option, so it's custom
            return 'custom';
        }
    }

    /**
     * Convert number to Persian words
     */
    private function numberToPersianWords($number)
    {
        $ones = [
            0 => '',
            1 => 'یک',
            2 => 'دو',
            3 => 'سه',
            4 => 'چهار',
            5 => 'پنج',
            6 => 'شش',
            7 => 'هفت',
            8 => 'هشت',
            9 => 'نه',
            10 => 'ده',
            11 => 'یازده',
            12 => 'دوازده',
            13 => 'سیزده',
            14 => 'چهارده',
            15 => 'پانزده',
            16 => 'شانزده',
            17 => 'هفده',
            18 => 'هجده',
            19 => 'نوزده',
        ];

        $tens = [
            2 => 'بیست',
            3 => 'سی',
            4 => 'چهل',
            5 => 'پنجاه',
            6 => 'شصت',
            7 => 'هفتاد',
            8 => 'هشتاد',
            9 => 'نود',
        ];

        $hundreds = [
            1 => 'یکصد',
            2 => 'دویست',
            3 => 'سیصد',
            4 => 'چهارصد',
            5 => 'پانصد',
            6 => 'ششصد',
            7 => 'هفتصد',
            8 => 'هشتصد',
            9 => 'نهصد',
        ];

        if ($number == 0) {
            return 'صفر';
        }

        // Handle negative numbers
        $isNegative = $number < 0;
        $number = abs($number);

        // Split into integer and decimal parts
        $parts = explode('.', (string)$number);
        $integerPart = (int)$parts[0];
        $decimalPart = isset($parts[1]) ? (int)substr($parts[1], 0, 2) : 0;

        $result = '';

        // Convert integer part
        if ($integerPart >= 1000000000) {
            $billions = (int)($integerPart / 1000000000);
            $result .= $this->convertThreeDigits($billions, $ones, $tens, $hundreds) . ' میلیارد ';
            $integerPart = $integerPart % 1000000000;
        }

        if ($integerPart >= 1000000) {
            $millions = (int)($integerPart / 1000000);
            $result .= $this->convertThreeDigits($millions, $ones, $tens, $hundreds) . ' میلیون ';
            $integerPart = $integerPart % 1000000;
        }

        if ($integerPart >= 1000) {
            $thousands = (int)($integerPart / 1000);
            if ($thousands == 1) {
                $result .= 'هزار ';
            } else {
                $result .= $this->convertThreeDigits($thousands, $ones, $tens, $hundreds) . ' هزار ';
            }
            $integerPart = $integerPart % 1000;
        }

        if ($integerPart > 0) {
            $result .= $this->convertThreeDigits($integerPart, $ones, $tens, $hundreds);
        }

        // Remove trailing space
        $result = trim($result);

        // Add negative prefix if needed
        if ($isNegative) {
            $result = 'منفی ' . $result;
        }

        return $result;
    }

    /**
     * Convert three-digit number to words
     */
    private function convertThreeDigits($number, $ones, $tens, $hundreds)
    {
        $result = '';

        if ($number >= 100) {
            $hundred = (int)($number / 100);
            $result .= $hundreds[$hundred] . ' ';
            $number = $number % 100;
        }

        if ($number >= 20) {
            $ten = (int)($number / 10);
            $result .= $tens[$ten] . ' ';
            $number = $number % 10;
        }

        if ($number > 0) {
            $result .= $ones[$number] . ' ';
        }

        return trim($result);
    }

    /**
     * Send debt notification SMS to building manager
     */
    public function sendDebtSms(Request $request, $building)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify building belongs to the organization - support both ID and slug
        $buildingQuery = \App\Models\Building::where('organization_id', $user->organization_id);
        
        // Check if $building is numeric (ID) or string (slug)
        if (is_numeric($building)) {
            $building = $buildingQuery->findOrFail($building);
        } else {
            $building = $buildingQuery->where('slug', $building)->firstOrFail();
        }

        // Check if building has manager phone
        if (!$building->manager_phone) {
            return response()->json([
                'success' => false,
                'message' => 'شماره تماس مدیر ساختمان ثبت نشده است.'
            ], 400);
        }

        // Get organization
        $organization = Organization::findOrFail($user->organization_id);

        // Calculate current debt (balance - if negative, it's debt)
        $balance = BuildingFinancialRecord::calculateBalance($building->id);
        $debtValue = $balance < 0 ? abs($balance) : 0;

        // Get current Jalali date
        $dateValue = Jalalian::now()->format('Y/m/d');

        // Send SMS
        $smsResult = $this->smsService->sendBuildingManagerDebtSms(
            $organization,
            $building->manager_phone,
            $building->name,
            $debtValue,
            $dateValue,
            true // Use queue
        );

        if (!$smsResult['success']) {
            Log::error('Building manager debt SMS failed', [
                'building_id' => $building->id,
                'phone_number' => $building->manager_phone,
                'debt_value' => $debtValue,
                'error' => $smsResult['error'] ?? 'Unknown error',
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $smsResult['message'] ?? 'خطا در ارسال پیامک',
                'error' => $smsResult['error'] ?? 'Unknown error'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'پیامک با موفقیت ارسال شد.',
            'data' => [
                'debt_value' => $debtValue,
                'sms_count' => $smsResult['sms_count'] ?? 0,
                'cost' => $smsResult['cost'] ?? 0,
            ]
        ]);
    }
}
