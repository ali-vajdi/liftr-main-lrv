<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\BuildingContract;
use App\Models\BuildingFinancialRecord;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class BuildingContractController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $buildingId)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $building = Building::where('organization_id', $user->organization_id)
            ->findOrFail($buildingId);

        $validator = Validator::make($request->all(), [
            'manager_name' => 'required|string|max:255',
            'manager_phone' => 'required|string|max:20',
            'contract_start_date' => 'required|string',
            'contract_end_date' => 'required|string',
            'monthly_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:1,2,3,4,5,6,custom',
            'payment_timing' => 'required|in:after_service,before_service,at_contract_time',
            'payment_frequency_value' => 'required|integer|min:1',
            'previous_debt' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Convert Jalali dates to Gregorian
        try {
            $jalaliDate = Jalalian::fromFormat('Y/m/d', $data['contract_start_date']);
            $data['contract_start_date'] = $jalaliDate->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date format for contract_start_date',
                'errors' => ['contract_start_date' => ['فرمت تاریخ نامعتبر است']]
            ], 422);
        }

        try {
            $jalaliDate = Jalalian::fromFormat('Y/m/d', $data['contract_end_date']);
            $data['contract_end_date'] = $jalaliDate->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date format for contract_end_date',
                'errors' => ['contract_end_date' => ['فرمت تاریخ نامعتبر است']]
            ], 422);
        }

        // Calculate annual amount
        $data['annual_amount'] = $data['monthly_amount'] * 12;

        // Map payment method to database fields
        // Payment fields are already in $data (auto-filled by frontend for predefined options)
        $paymentMethod = $data['payment_method'];
        unset($data['payment_method']);

        // If not custom, ensure fields are set from predefined mapping
        if ($paymentMethod !== 'custom') {
            // Map predefined options (frontend should have already filled these, but ensure they're correct)
            switch ($paymentMethod) {
                case '1': // ماهانه بعد از انجام سرویس
                    $data['payment_timing'] = 'after_service';
                    $data['payment_frequency_value'] = 1;
                    break;
                case '2': // 2ماه یکبار بعد از انجام سرویس
                    $data['payment_timing'] = 'after_service';
                    $data['payment_frequency_value'] = 2;
                    break;
                case '3': // 3 ماه یکبار بعد از انجام سرویس
                    $data['payment_timing'] = 'after_service';
                    $data['payment_frequency_value'] = 3;
                    break;
                case '4': // 3 ماه یکبار قبل از انجام سرویس
                    $data['payment_timing'] = 'before_service';
                    $data['payment_frequency_value'] = 3;
                    break;
                case '5': // 6ماه یکبار قبل از انجام سرویس
                    $data['payment_timing'] = 'before_service';
                    $data['payment_frequency_value'] = 6;
                    break;
                case '6': // یکساله زمان عقد قرارداد
                    $data['payment_timing'] = 'before_service';
                    $data['payment_frequency_value'] = 12;
                    break;
            }
        }
        // For custom, payment_timing and payment_frequency_value are already in $data

        $data['previous_debt'] = $data['previous_debt'] ?? 0;
        $data['building_id'] = $building->id;

        DB::beginTransaction();
        try {
            // Check if there's an active contract
            $activeContract = BuildingContract::where('building_id', $building->id)
                ->where('status', BuildingContract::STATUS_ACTIVE)
                ->first();
            
            // If there's an active contract and user wants to finish it
            if ($activeContract && $request->has('finish_old_contract') && $request->finish_old_contract) {
                // Finish the old contract
                $activeContract->status = BuildingContract::STATUS_FINISHED;
                $activeContract->save();
                
                // Mark pending services as expired (only pending services)
                Service::where('building_contract_id', $activeContract->id)
                    ->where('status', Service::STATUS_PENDING)
                    ->update([
                        'status' => Service::STATUS_EXPIRED,
                    ]);
                
                // Cancel pending services if requested (this will override the expired status)
                if ($request->has('cancel_pending_services') && $request->cancel_pending_services) {
                    Service::where('building_contract_id', $activeContract->id)
                        ->where('status', Service::STATUS_EXPIRED)
                        ->update([
                            'status' => Service::STATUS_CANCELLED,
                            'technician_id' => null,
                            'assigned_at' => null,
                        ]);
                }
            } elseif ($activeContract) {
                // Active contract exists but user didn't request to finish it
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'قرارداد فعالی برای این ساختمان وجود دارد. لطفاً ابتدا قرارداد فعلی را تمام شده یا لغو شده کنید.',
                    'errors' => ['contract' => ['قرارداد فعال موجود است']]
                ], 422);
            }
            
            // Calculate organization-specific contract number
            // Get all contracts for buildings in this organization
            $maxContractNumber = BuildingContract::whereHas('building', function($q) use ($building) {
                $q->where('organization_id', $building->organization_id);
            })->max('contract_number');
            
            // Assign next contract number (starting from 1 if no contracts exist)
            $data['contract_number'] = ($maxContractNumber ?? 0) + 1;
            
            // Extract manager fields before creating contract
            $managerName = $data['manager_name'];
            $managerPhone = $data['manager_phone'];
            unset($data['manager_name'], $data['manager_phone']);
            
            $data['status'] = BuildingContract::STATUS_ACTIVE;
            $contract = BuildingContract::create($data);

            // Update building manager information
            $building->manager_name = $managerName;
            $building->manager_phone = $managerPhone;
            $building->save();

            // Generate services for all months in the contract period
            $contract->generateServices();

            // Create financial record for previous_debt if exists
            if ($contract->previous_debt && $contract->previous_debt > 0) {
                BuildingFinancialRecord::create([
                    'building_id' => $building->id,
                    'building_contract_id' => $contract->id,
                    'type' => BuildingFinancialRecord::TYPE_DEBIT,
                    'amount' => $contract->previous_debt,
                    'transaction_type' => BuildingFinancialRecord::TRANSACTION_PREVIOUS_DEBT,
                    'description' => 'بدهی قبلی تا تاریخ ' . Jalalian::forge(now())->format('Y/m/d'),
                    'transaction_date' => now(),
                ]);
            }

            DB::commit();

            // Add Jalali formatted dates
            if ($contract->contract_start_date) {
                $contract->contract_start_date_jalali = Jalalian::forge($contract->contract_start_date)->format('Y/m/d');
            }
            if ($contract->contract_end_date) {
                $contract->contract_end_date_jalali = Jalalian::forge($contract->contract_end_date)->format('Y/m/d');
            }

            return response()->json([
                'success' => true,
                'message' => 'قرارداد با موفقیت ذخیره شد',
                'data' => $contract
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطا در ذخیره قرارداد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of contracts for a building.
     */
    public function index(string $buildingId)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $building = Building::where('organization_id', $user->organization_id)
            ->findOrFail($buildingId);

        $contracts = BuildingContract::where('building_id', $building->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Add Jalali formatted dates and payment method mapping
        $contracts->map(function ($contract) {
            if ($contract->contract_start_date) {
                $contract->contract_start_date_jalali = Jalalian::forge($contract->contract_start_date)->format('Y/m/d');
            }
            if ($contract->contract_end_date) {
                $contract->contract_end_date_jalali = Jalalian::forge($contract->contract_end_date)->format('Y/m/d');
            }
            
            // Map payment method
            $contract = $this->mapPaymentMethod($contract);
            
            return $contract;
        });

        return response()->json([
            'success' => true,
            'data' => $contracts
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $buildingId, string $contractId)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $building = Building::where('organization_id', $user->organization_id)
            ->findOrFail($buildingId);

        $contract = BuildingContract::where('building_id', $building->id)
            ->findOrFail($contractId);

        // Add Jalali formatted dates
        if ($contract->contract_start_date) {
            $contract->contract_start_date_jalali = Jalalian::forge($contract->contract_start_date)->format('Y/m/d');
        }
        if ($contract->contract_end_date) {
            $contract->contract_end_date_jalali = Jalalian::forge($contract->contract_end_date)->format('Y/m/d');
        }

        // Add Jalali formatted dates
        if ($contract->contract_start_date) {
            $contract->contract_start_date_jalali = Jalalian::forge($contract->contract_start_date)->format('Y/m/d');
        }
        if ($contract->contract_end_date) {
            $contract->contract_end_date_jalali = Jalalian::forge($contract->contract_end_date)->format('Y/m/d');
        }

        // Map payment method
        $contract = $this->mapPaymentMethod($contract);

        return response()->json([
            'success' => true,
            'data' => $contract
        ]);
    }

    /**
     * Update contract status (finish or cancel)
     */
    public function updateStatus(Request $request, string $buildingId, string $contractId)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $building = Building::where('organization_id', $user->organization_id)
            ->findOrFail($buildingId);

        $contract = BuildingContract::where('building_id', $building->id)
            ->findOrFail($contractId);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:finished,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $contract->status = $request->status === 'finished' ? BuildingContract::STATUS_FINISHED : BuildingContract::STATUS_CANCELLED;
            $contract->save();

            // If contract is finished, mark only pending services as expired
            if ($request->status === 'finished') {
                Service::where('building_contract_id', $contract->id)
                    ->where('status', Service::STATUS_PENDING)
                    ->update([
                        'status' => Service::STATUS_EXPIRED,
                    ]);
            }
            // If contract is cancelled, cancel only pending services for this contract
            elseif ($request->status === 'cancelled') {
                Service::where('building_contract_id', $contract->id)
                    ->where('status', Service::STATUS_PENDING)
                    ->update([
                        'status' => Service::STATUS_CANCELLED,
                        'technician_id' => null,
                        'assigned_at' => null,
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->status === 'finished' ? 'قرارداد به عنوان تمام شده ثبت شد' : 'قرارداد لغو شد',
                'data' => $contract
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی وضعیت قرارداد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map database fields to payment method selection
     * Determines if contract matches a predefined option or is custom
     */
    private function mapPaymentMethod($contract)
    {
        // Determine which predefined option matches based on field values
        if ($contract->payment_timing === 'after_service' && $contract->payment_frequency_value == 1) {
            $contract->payment_method = '1';
        } elseif ($contract->payment_timing === 'after_service' && $contract->payment_frequency_value == 2) {
            $contract->payment_method = '2';
        } elseif ($contract->payment_timing === 'after_service' && $contract->payment_frequency_value == 3) {
            $contract->payment_method = '3';
        } elseif ($contract->payment_timing === 'before_service' && $contract->payment_frequency_value == 3) {
            $contract->payment_method = '4';
        } elseif ($contract->payment_timing === 'before_service' && $contract->payment_frequency_value == 6) {
            $contract->payment_method = '5';
        } elseif ($contract->payment_timing === 'before_service' && $contract->payment_frequency_value == 12) {
            $contract->payment_method = '6';
        } else {
            // Doesn't match any predefined option, so it's custom
            $contract->payment_method = 'custom';
        }
        
        return $contract;
    }

    /**
     * Check if building has pending services in last contract and active contract status
     */
    public function checkPendingServices(string $buildingId)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $building = Building::where('organization_id', $user->organization_id)
            ->findOrFail($buildingId);

        // Get the active contract
        $activeContract = BuildingContract::where('building_id', $building->id)
            ->where('status', BuildingContract::STATUS_ACTIVE)
            ->first();

        if (!$activeContract) {
            return response()->json([
                'success' => true,
                'data' => [
                    'has_active_contract' => false,
                    'has_pending_services' => false,
                    'pending_count' => 0,
                    'contract_id' => null
                ]
            ]);
        }

        // Count pending services for this contract
        $pendingCount = Service::where('building_contract_id', $activeContract->id)
            ->where('status', Service::STATUS_PENDING)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'has_active_contract' => true,
                'has_pending_services' => $pendingCount > 0,
                'pending_count' => $pendingCount,
                'contract_id' => $activeContract->id
            ]
        ]);
    }

    /**
     * Cancel pending services for a contract
     */
    public function cancelPendingServices(Request $request, string $buildingId, string $contractId)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $building = Building::where('organization_id', $user->organization_id)
            ->findOrFail($buildingId);

        $contract = BuildingContract::where('building_id', $building->id)
            ->findOrFail($contractId);

        DB::beginTransaction();
        try {
            // Cancel all pending services for this contract
            $cancelledCount = Service::where('building_contract_id', $contract->id)
                ->where('status', Service::STATUS_PENDING)
                ->update([
                    'status' => Service::STATUS_CANCELLED,
                    'technician_id' => null,
                    'assigned_at' => null,
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$cancelledCount} سرویس در انتظار لغو شد",
                'data' => [
                    'cancelled_count' => $cancelledCount
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطا در لغو سرویس‌های در انتظار',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
