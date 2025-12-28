<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\BuildingContract;
use App\Models\BuildingFinancialRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class FinancialDashboardController extends Controller
{
    /**
     * Get financial dashboard data - financial records for a specific building
     */
    public function index(Request $request, $building)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify building belongs to the organization
        $building = \App\Models\Building::where('organization_id', $user->organization_id)
            ->findOrFail($building);

        // Get all financial records for this building, ordered by transaction_date (or created_at) ascending for balance calculation
        $records = BuildingFinancialRecord::where('building_id', $building->id)
            ->orderByRaw('COALESCE(transaction_date, created_at) ASC')
            ->orderBy('id', 'ASC')
            ->get();

        // Calculate cumulative balance
        $cumulativeBalance = 0;
        $formattedRecords = $records->map(function ($record) use (&$cumulativeBalance) {
            // Calculate balance: debit (بدهکاری) is negative, credit (بستانکاری) is positive
            if ($record->type === BuildingFinancialRecord::TYPE_DEBIT) {
                $cumulativeBalance -= $record->amount;
            } else {
                $cumulativeBalance += $record->amount;
            }

            // Get transaction date (prefer transaction_date, fallback to created_at)
            $transactionDate = $record->transaction_date ?? $record->created_at;

            return [
                'id' => $record->id,
                'transaction_date' => $transactionDate ? $transactionDate->toIso8601String() : null,
                'transaction_date_jalali' => $transactionDate ? Jalalian::forge($transactionDate)->format('Y/m/d H:i:s') : null,
                'description' => $record->description,
                'debit' => $record->type === BuildingFinancialRecord::TYPE_DEBIT ? $record->amount : null,
                'credit' => $record->type === BuildingFinancialRecord::TYPE_CREDIT ? $record->amount : null,
                'balance' => $cumulativeBalance,
                'extra_descriptions' => $record->extra_descriptions,
            ];
        });

        // Reverse to show newest first in the table
        $formattedRecords = $formattedRecords->reverse()->values();

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
}
