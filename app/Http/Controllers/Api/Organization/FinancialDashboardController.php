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

        // Get all financial records for this building
        $records = BuildingFinancialRecord::where('building_id', $building->id)
            ->with(['buildingContract', 'service'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate balance
        $balance = BuildingFinancialRecord::calculateBalance($building->id);
        $pendingAmount = BuildingFinancialRecord::calculatePendingAmount($building->id);

        // Format records
        $formattedRecords = $records->map(function ($record) {
            return [
                'id' => $record->id,
                'type' => $record->type,
                'type_text' => $record->type_text,
                'amount' => $record->amount,
                'transaction_type' => $record->transaction_type,
                'transaction_type_text' => $record->transaction_type_text,
                'description' => $record->description,
                'service_month' => $record->service_month,
                'service_year' => $record->service_year,
                'service_date_text' => $record->service_month && $record->service_year 
                    ? $this->getServiceDateText($record->service_month, $record->service_year) 
                    : null,
                'is_pending' => $record->is_pending,
                'transaction_date' => $record->transaction_date ? $record->transaction_date->toIso8601String() : null,
                'transaction_date_jalali' => $record->transaction_date ? Jalalian::forge($record->transaction_date)->format('Y/m/d H:i:s') : null,
                'contract_id' => $record->building_contract_id,
                'service_id' => $record->service_id,
                'created_at' => $record->created_at->toIso8601String(),
                'created_at_jalali' => Jalalian::forge($record->created_at)->format('Y/m/d H:i:s'),
            ];
        });

        // Calculate totals
        $totalDebits = $records->where('type', BuildingFinancialRecord::TYPE_DEBIT)->sum('amount');
        $totalCredits = $records->where('type', BuildingFinancialRecord::TYPE_CREDIT)->sum('amount');
        $totalPendingDebits = $records->where('type', BuildingFinancialRecord::TYPE_DEBIT)
            ->where('is_pending', true)
            ->sum('amount');

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
            'is_pending' => false, // Manual transactions are not pending
            'transaction_date' => $transactionDate,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تراکنش مالی با موفقیت ثبت شد',
            'data' => $record
        ], 201);
    }

    /**
     * Update pending status of a financial record (mark as paid)
     */
    public function updatePendingStatus(Request $request, $recordId)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $record = BuildingFinancialRecord::whereHas('building', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            })
            ->findOrFail($recordId);

        $validator = Validator::make($request->all(), [
            'is_pending' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $record->is_pending = $request->is_pending;
        if (!$request->is_pending) {
            $record->transaction_date = now();
        }
        $record->save();

        return response()->json([
            'success' => true,
            'message' => $request->is_pending ? 'وضعیت به در انتظار پرداخت تغییر یافت' : 'وضعیت به پرداخت شده تغییر یافت',
            'data' => $record
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
}
