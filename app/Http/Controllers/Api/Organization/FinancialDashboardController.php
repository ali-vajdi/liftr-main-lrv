<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\BuildingContract;
use App\Models\BuildingFinancialRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

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
            'description' => 'nullable|string|max:1000',
            'extra_descriptions' => 'nullable|string|max:2000',
            'transaction_date' => 'nullable|date',
        ], [
            'type.required' => 'نوع تراکنش الزامی است',
            'type.in' => 'نوع تراکنش نامعتبر است',
            'amount.required' => 'مبلغ الزامی است',
            'amount.numeric' => 'مبلغ باید عدد باشد',
            'amount.min' => 'مبلغ باید بیشتر از 0 باشد',
            'transaction_type.required' => 'نوع تراکنش مالی الزامی است',
            'transaction_type.in' => 'نوع تراکنش مالی نامعتبر است',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $transactionDate = $request->transaction_date 
            ? \Carbon\Carbon::parse($request->transaction_date) 
            : now();

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
}
