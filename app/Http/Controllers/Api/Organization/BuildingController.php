<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\BuildingContract;
use App\Models\Elevator;
use App\Models\Province;
use App\Models\City;
use App\Models\Service;
use App\Models\ServiceView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $organizationId = $user->organization_id;

        $query = Building::with(['province', 'city', 'organizationUser'])
            ->where('organization_id', $organizationId);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%")
                  ->orWhere('manager_phone', 'like', "%{$search}%");
            });
        }

        // Filter by building type
        if ($request->has('building_type') && $request->building_type) {
            $query->where('building_type', $request->building_type);
        }

        // Filter by province
        if ($request->has('province_id') && $request->province_id) {
            $query->where('province_id', $request->province_id);
        }

        // Filter by city
        if ($request->has('city_id') && $request->city_id) {
            $query->where('city_id', $request->city_id);
        }

        // Filter by status (only if not 'all')
        if ($request->has('status') && $request->status !== '' && $request->status !== 'all') {
            $query->where('status', $request->status === 'true' || $request->status === true);
        }

        // Filter expiring contracts (contract_end_date within next N days, default 30)
        if ($request->has('expiring') && $request->expiring === 'true') {
            $today = Carbon::today();
            $days = $request->has('days') ? (int)$request->days : 30;
            $endDate = Carbon::today()->addDays($days);
            $query->whereHas('contract', function($q) use ($today, $endDate) {
                $q->whereNotNull('contract_end_date')
                  ->whereBetween('contract_end_date', [$today, $endDate])
                  ->where('contract_end_date', '>=', $today);
            });
            // Note: We can't order by contract_end_date directly, so we'll order by created_at
            $query->orderBy('created_at', 'desc');
        } 
        // Filter expired contracts (contract_end_date is in the past)
        elseif ($request->has('expired') && $request->expired === 'true') {
            $today = Carbon::today();
            $query->whereHas('contract', function($q) use ($today) {
                $q->whereNotNull('contract_end_date')
                  ->where('contract_end_date', '<', $today);
            });
            $query->orderBy('created_at', 'desc');
        } 
        else {
            $query->orderBy('created_at', 'desc');
        }

        $buildings = $query->with('contract')->paginate(10);
        
        $today = Carbon::today();
        
        // Add Jalali formatted dates and calculate days difference from contract
        $items = collect($buildings->items())->map(function ($building) use ($today) {
            if ($building->contract) {
                if ($building->contract->contract_start_date) {
                    $building->contract_start_date_jalali = Jalalian::forge($building->contract->contract_start_date)->format('Y/m/d');
                }
                if ($building->contract->contract_end_date) {
                    $building->contract_end_date_jalali = Jalalian::forge($building->contract->contract_end_date)->format('Y/m/d');
                    
                    // Calculate days difference
                    $endDate = Carbon::parse($building->contract->contract_end_date);
                    $diffDays = $today->diffInDays($endDate, false); // false = signed difference
                    
                    if ($diffDays < 0) {
                        // Expired - days past
                        $building->days_past = abs($diffDays);
                        $building->days_remaining = null;
                    } else {
                        // Not expired - days remaining
                        $building->days_remaining = $diffDays;
                        $building->days_past = null;
                    }
                } else {
                    $building->days_remaining = null;
                    $building->days_past = null;
                }
            } else {
                $building->days_remaining = null;
                $building->days_past = null;
            }
            return $building;
        });

        return response()->json([
            'success' => true,
            'data' => $items->all(),
            'pagination' => [
                'current_page' => $buildings->currentPage(),
                'last_page' => $buildings->lastPage(),
                'per_page' => $buildings->perPage(),
                'total' => $buildings->total(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'manager_name' => 'required|string|max:255',
            'manager_phone' => 'required|string|max:20',
            'building_type' => 'required|in:residential,office,commercial',
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:cities,id',
            'address' => 'required|string',
            'selected_latitude' => 'nullable|numeric|between:-90,90',
            'selected_longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:true,false',
            'elevators_count' => 'nullable|integer|min:0',
            'elevators' => 'required|array|min:1',
            'elevators.*.name' => 'required_with:elevators|string|max:255',
            'elevators.*.stops_count' => 'required_with:elevators|integer|min:1',
            'elevators.*.capacity' => 'required_with:elevators|integer|min:1',
            'elevators.*.status' => 'required_with:elevators|in:true,false',
            'elevators.*.description' => 'nullable|string',
            // Contract fields
            'contract_start_date' => 'required|string',
            'contract_end_date' => 'required|string',
            'contract_monthly_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:1,2,3,4,5,6,custom',
            'payment_timing' => 'required_if:payment_method,custom|in:after_service,before_service,at_contract_time',
            'payment_frequency_type' => 'required_if:payment_method,custom|in:monthly,yearly',
            'payment_frequency_value' => 'required_if:payment_method,custom|integer|min:1',
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
        $data['organization_id'] = $user->organization_id;
        $data['organization_user_id'] = $user->id;
        $data['status'] = $data['status'] === 'true' || $data['status'] === true;
        $data['elevators_count'] = $data['elevators_count'] ?? 0;

        // Extract contract data
        $contractData = [
            'contract_start_date' => $data['contract_start_date'],
            'contract_end_date' => $data['contract_end_date'],
            'monthly_amount' => $data['contract_monthly_amount'],
            'payment_method' => $data['payment_method'],
            'previous_debt' => $data['previous_debt'] ?? 0,
        ];
        
        if ($data['payment_method'] === 'custom') {
            $contractData['payment_timing'] = $data['payment_timing'];
            $contractData['payment_frequency_type'] = $data['payment_frequency_type'];
            $contractData['payment_frequency_value'] = $data['payment_frequency_value'];
        }
        
        // Remove contract fields from building data
        unset($data['contract_start_date'], $data['contract_end_date'], $data['contract_monthly_amount'], 
              $data['payment_method'], $data['payment_timing'], $data['payment_frequency_type'], 
              $data['payment_frequency_value'], $data['previous_debt']);

        // Extract elevators data (required for new buildings)
        $elevatorsData = $request->input('elevators', []);
        unset($data['elevators']);

        // Validate that at least one elevator is provided
        if (empty($elevatorsData) || count($elevatorsData) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'elevators' => ['حداقل یک آسانسور الزامی است. لطفاً قبل از ایجاد ساختمان، آسانسورها را اضافه کنید.']
                ]
            ], 422);
        }

        DB::beginTransaction();
        try {
            $building = Building::create($data);
            
            // Create elevators (required)
            foreach ($elevatorsData as $elevatorData) {
                Elevator::create([
                    'building_id' => $building->id,
                    'name' => $elevatorData['name'],
                    'stops_count' => $elevatorData['stops_count'],
                    'capacity' => $elevatorData['capacity'],
                    'status' => $elevatorData['status'] === 'true' || $elevatorData['status'] === true,
                    'description' => $elevatorData['description'] ?? null,
                ]);
            }
            // Update elevators_count based on actual count
            $building->elevators_count = count($elevatorsData);
            $building->save();
            
            // Create contract
            $contractData['building_id'] = $building->id;
            $contractData['status'] = BuildingContract::STATUS_ACTIVE;
            
            // Convert Jalali dates to Gregorian
            try {
                $jalaliDate = Jalalian::fromFormat('Y/m/d', $contractData['contract_start_date']);
                $contractData['contract_start_date'] = $jalaliDate->toCarbon()->format('Y-m-d');
            } catch (\Exception $e) {
                throw new \Exception('Invalid date format for contract_start_date');
            }
            
            try {
                $jalaliDate = Jalalian::fromFormat('Y/m/d', $contractData['contract_end_date']);
                $contractData['contract_end_date'] = $jalaliDate->toCarbon()->format('Y-m-d');
            } catch (\Exception $e) {
                throw new \Exception('Invalid date format for contract_end_date');
            }
            
            // Calculate annual amount
            $contractData['annual_amount'] = $contractData['monthly_amount'] * 12;
            
            // Map payment method to database fields
            $paymentMethod = $contractData['payment_method'];
            unset($contractData['payment_method']);
            
            if ($paymentMethod === 'custom') {
                $contractData['is_custom_payment_method'] = true;
            } else {
                $contractData['is_custom_payment_method'] = false;
                // Map predefined options
                switch ($paymentMethod) {
                    case '1':
                        $contractData['payment_timing'] = 'after_service';
                        $contractData['payment_frequency_type'] = 'monthly';
                        $contractData['payment_frequency_value'] = 1;
                        break;
                    case '2':
                        $contractData['payment_timing'] = 'after_service';
                        $contractData['payment_frequency_type'] = 'monthly';
                        $contractData['payment_frequency_value'] = 2;
                        break;
                    case '3':
                        $contractData['payment_timing'] = 'after_service';
                        $contractData['payment_frequency_type'] = 'monthly';
                        $contractData['payment_frequency_value'] = 3;
                        break;
                    case '4':
                        $contractData['payment_timing'] = 'before_service';
                        $contractData['payment_frequency_type'] = 'monthly';
                        $contractData['payment_frequency_value'] = 3;
                        break;
                    case '5':
                        $contractData['payment_timing'] = 'before_service';
                        $contractData['payment_frequency_type'] = 'monthly';
                        $contractData['payment_frequency_value'] = 6;
                        break;
                    case '6':
                        $contractData['payment_timing'] = 'at_contract_time';
                        $contractData['payment_frequency_type'] = 'yearly';
                        $contractData['payment_frequency_value'] = 1;
                        break;
                }
            }
            
            BuildingContract::create($contractData);
            
            DB::commit();
            
            $building = $building->load(['province', 'city', 'organizationUser', 'elevators', 'activeContract']);

            return response()->json([
                'success' => true,
                'message' => 'ساختمان/پروژه با موفقیت ایجاد شد',
                'data' => $building
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد ساختمان/پروژه',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $building = Building::with(['province', 'city', 'organizationUser', 'elevators'])
            ->where('organization_id', $user->organization_id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $building
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $building = Building::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'manager_name' => 'required|string|max:255',
            'manager_phone' => 'required|string|max:20',
            'building_type' => 'required|in:residential,office,commercial',
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:cities,id',
            'address' => 'required|string',
            'selected_latitude' => 'nullable|numeric|between:-90,90',
            'selected_longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:true,false',
            'elevators_count' => 'nullable|integer|min:0',
            'elevators' => 'nullable|array',
            'elevators.*.name' => 'required_with:elevators|string|max:255',
            'elevators.*.stops_count' => 'required_with:elevators|integer|min:1',
            'elevators.*.capacity' => 'required_with:elevators|integer|min:1',
            'elevators.*.status' => 'required_with:elevators|in:true,false',
            'elevators.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['status'] = $data['status'] === 'true' || $data['status'] === true;
        $data['elevators_count'] = $data['elevators_count'] ?? $building->elevators_count ?? 0;

        // Extract elevators data if provided
        $elevatorsData = $request->has('elevators') ? $request->input('elevators', []) : null;
        unset($data['elevators']);

        DB::beginTransaction();
        try {
            $building->update($data);
            
            // Handle elevators if provided (null means don't update, empty array means delete all)
            if ($elevatorsData !== null) {
                // Get existing elevator IDs
                $existingElevatorIds = $building->elevators()->pluck('id')->toArray();
                $submittedElevatorIds = [];
                
                // Update or create elevators
                foreach ($elevatorsData as $elevatorData) {
                    if (isset($elevatorData['id']) && !empty($elevatorData['id']) && in_array($elevatorData['id'], $existingElevatorIds)) {
                        // Update existing elevator
                        $elevator = Elevator::where('building_id', $building->id)
                            ->findOrFail($elevatorData['id']);
                        $elevator->update([
                            'name' => $elevatorData['name'],
                            'stops_count' => $elevatorData['stops_count'],
                            'capacity' => $elevatorData['capacity'],
                            'status' => $elevatorData['status'] === 'true' || $elevatorData['status'] === true,
                            'description' => $elevatorData['description'] ?? null,
                        ]);
                        $submittedElevatorIds[] = $elevatorData['id'];
                    } else {
                        // Create new elevator
                        Elevator::create([
                            'building_id' => $building->id,
                            'name' => $elevatorData['name'],
                            'stops_count' => $elevatorData['stops_count'],
                            'capacity' => $elevatorData['capacity'],
                            'status' => $elevatorData['status'] === 'true' || $elevatorData['status'] === true,
                            'description' => $elevatorData['description'] ?? null,
                        ]);
                    }
                }
                
                // Delete elevators that are not in the submitted list
                $toDelete = array_diff($existingElevatorIds, $submittedElevatorIds);
                if (!empty($toDelete)) {
                    Elevator::where('building_id', $building->id)
                        ->whereIn('id', $toDelete)
                        ->delete();
                }
                
                // Update elevators_count based on actual count
                $building->elevators_count = count($elevatorsData);
                $building->save();
            }
            
            DB::commit();
            
            $building = $building->load(['province', 'city', 'organizationUser', 'elevators']);
            
            // Add Jalali formatted dates
            if ($building->service_start_date) {
                $building->service_start_date_jalali = Jalalian::forge($building->service_start_date)->format('Y/m/d');
            }
            if ($building->service_end_date) {
                $building->service_end_date_jalali = Jalalian::forge($building->service_end_date)->format('Y/m/d');
            }

            return response()->json([
                'success' => true,
                'message' => 'ساختمان/پروژه با موفقیت به‌روزرسانی شد',
                'data' => $building
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی ساختمان/پروژه',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $building = Building::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        $building->delete();

        return response()->json([
            'success' => true,
            'message' => 'ساختمان/پروژه با موفقیت حذف شد'
        ]);
    }

    /**
     * Get provinces for dropdown
     */
    public function getProvinces()
    {
        $provinces = Province::select('id', 'name', 'name_en')->get();
        
        return response()->json([
            'success' => true,
            'data' => $provinces
        ]);
    }

    /**
     * Get cities by province
     */
    public function getCitiesByProvince(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'province_id' => 'required|exists:provinces,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $cities = City::where('province_id', $request->province_id)
            ->select('id', 'name', 'name_en', 'latitude', 'longitude')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    /**
     * Get building dashboard data
     */
    public function dashboard(Request $request, Building $building)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify building belongs to user's organization
        if ($building->organization_id !== $user->organization_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Load relationships
        $building->load(['province', 'city', 'organizationUser', 'elevators', 'contract']);

        // Get all services with complete relationships and filters
        $servicesQuery = Service::with([
            'technician',
            'checklist' => function($query) {
                $query->with([
                    'elevatorChecklists' => function($q) {
                        $q->with([
                            'elevator',
                            'descriptions.checklist'
                        ]);
                    },
                    'managerSignature',
                    'technicianSignature'
                ]);
            }
        ])
        ->where('building_id', $building->id);

        // Apply date filters (filter by service created_at date)
        if ($request->has('date_from') && !empty($request->date_from)) {
            try {
                $jalaliDate = Jalalian::fromFormat('Y/m/d', $request->date_from);
                $georgianDate = $jalaliDate->toCarbon()->startOfDay();
                $servicesQuery->where('created_at', '>=', $georgianDate);
            } catch (\Exception $e) {
                // If date conversion fails, skip the filter
            }
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            try {
                $jalaliDate = Jalalian::fromFormat('Y/m/d', $request->date_to);
                $georgianDate = $jalaliDate->toCarbon()->endOfDay();
                $servicesQuery->where('created_at', '<=', $georgianDate);
            } catch (\Exception $e) {
                // If date conversion fails, skip the filter
            }
        }

        // Apply service status filter
        if ($request->has('service_status') && !empty($request->service_status)) {
            $servicesQuery->where('status', $request->service_status);
        }

        // Apply technician filter
        if ($request->has('technician_id') && !empty($request->technician_id)) {
            $servicesQuery->where('technician_id', $request->technician_id);
        }

        // Apply service year filter
        if ($request->has('service_year') && !empty($request->service_year)) {
            $servicesQuery->where('service_year', $request->service_year);
        }

        // Apply service month filter
        if ($request->has('service_month') && !empty($request->service_month)) {
            $servicesQuery->where('service_month', $request->service_month);
        }

        $services = $servicesQuery
        ->orderBy('service_year', 'desc')
        ->orderBy('service_month', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();

        // Format services data
        $monthNames = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
            5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
            9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند'
        ];

        $formattedServices = $services->map(function($service) use ($monthNames) {
            $serviceData = [
                'id' => $service->id,
                'service_month' => $service->service_month,
                'service_year' => $service->service_year,
                'service_date_text' => ($monthNames[$service->service_month] ?? $service->service_month) . ' ' . $service->service_year,
                'status' => $service->status,
                'status_text' => $service->status_text,
                'status_badge_class' => $service->status_badge_class,
                'is_manual' => $service->is_manual,
                'notes' => $service->notes,
                'organization_note' => $service->organization_note,
                'user_note' => $service->user_note,
                'technician_note' => $service->technician_note,
                'created_at' => $service->created_at ? $service->created_at->toIso8601String() : null,
                'created_at_jalali' => $service->created_at ? Jalalian::forge($service->created_at)->format('Y/m/d H:i:s') : null,
                'assigned_at' => $service->assigned_at ? $service->assigned_at->toIso8601String() : null,
                'assigned_at_jalali' => $service->assigned_at ? Jalalian::forge($service->assigned_at)->format('Y/m/d H:i:s') : null,
                'completed_at' => $service->completed_at ? $service->completed_at->toIso8601String() : null,
                'completed_at_jalali' => $service->completed_at ? Jalalian::forge($service->completed_at)->format('Y/m/d H:i:s') : null,
                'visit_date' => $service->visit_date ? $service->visit_date->format('Y-m-d') : null,
                'visit_date_jalali' => $service->visit_date ? Jalalian::forge($service->visit_date)->format('Y/m/d') : null,
                'visit_time_range' => $service->visit_time_range,
                'slug' => $service->slug,
                'technician' => null,
                'checklist' => null,
                'view_count' => 0,
                'views' => [],
            ];

            // Add view count and view details
            $views = ServiceView::where('service_id', $service->id)
                ->orderBy('viewed_at', 'desc')
                ->get();
            
            $serviceData['view_count'] = $views->count();
            $serviceData['views'] = $views->map(function ($view) {
                return [
                    'id' => $view->id,
                    'ip_address' => $view->ip_address,
                    'device_type' => $view->device_type,
                    'browser' => $view->browser,
                    'platform' => $view->platform,
                    'viewed_at' => $view->viewed_at ? Jalalian::forge($view->viewed_at)->format('Y/m/d H:i:s') : null,
                ];
            })->toArray();

            // Add technician info
            if ($service->technician) {
                $serviceData['technician'] = [
                    'id' => $service->technician->id,
                    'name' => $service->technician->name,
                    'first_name' => $service->technician->first_name,
                    'last_name' => $service->technician->last_name,
                    'phone' => $service->technician->phone,
                    'full_name' => $service->technician->name ?: 
                        ($service->technician->first_name && $service->technician->last_name ? 
                            $service->technician->first_name . ' ' . $service->technician->last_name : 
                            'نامشخص')
                ];
            }

            // Add checklist info
            if ($service->checklist) {
                $elevatorChecklists = $service->checklist->elevatorChecklists->map(function($elevatorChecklist) {
                    $elevatorData = [
                        'id' => $elevatorChecklist->id,
                        'verified' => $elevatorChecklist->verified,
                        'elevator' => null,
                        'descriptions' => []
                    ];

                    if ($elevatorChecklist->elevator) {
                        $elevatorData['elevator'] = [
                            'id' => $elevatorChecklist->elevator->id,
                            'name' => $elevatorChecklist->elevator->name,
                            'stops_count' => $elevatorChecklist->elevator->stops_count,
                            'capacity' => $elevatorChecklist->elevator->capacity,
                            'description' => $elevatorChecklist->elevator->description,
                        ];
                    }

                    if ($elevatorChecklist->descriptions) {
                        $elevatorData['descriptions'] = $elevatorChecklist->descriptions->map(function($desc) {
                            return [
                                'id' => $desc->id,
                                'title' => $desc->title,
                                'description' => $desc->description,
                                'checklist' => $desc->checklist ? [
                                    'id' => $desc->checklist->id,
                                    'title' => $desc->checklist->title,
                                ] : null
                            ];
                        })->toArray();
                    }

                    return $elevatorData;
                })->toArray();

                $serviceData['checklist'] = [
                    'id' => $service->checklist->id,
                    'submitted_at' => $service->checklist->submitted_at ? $service->checklist->submitted_at->toIso8601String() : null,
                    'submitted_at_jalali' => $service->checklist->submitted_at ? Jalalian::forge($service->checklist->submitted_at)->format('Y/m/d H:i:s') : null,
                    'elevator_checklists' => $elevatorChecklists,
                    'elevator_checklists_count' => count($elevatorChecklists),
                    'manager_signature' => $service->checklist->managerSignature ? [
                        'id' => $service->checklist->managerSignature->id,
                        'signature' => $service->checklist->managerSignature->signature,
                    ] : null,
                    'technician_signature' => $service->checklist->technicianSignature ? [
                        'id' => $service->checklist->technicianSignature->id,
                        'signature' => $service->checklist->technicianSignature->signature,
                    ] : null,
                ];
            }

            return $serviceData;
        })->toArray();

        // Calculate statistics (using filtered services)
        $totalServices = $services->count();
        $completedServices = $services->where('status', Service::STATUS_COMPLETED)->count();
        $pendingServices = $services->where('status', Service::STATUS_PENDING)->count();
        $assignedServices = $services->where('status', Service::STATUS_ASSIGNED)->count();
        $expiredServices = $services->where('status', Service::STATUS_EXPIRED)->count();
        $cancelledServices = $services->where('status', Service::STATUS_CANCELLED)->count();

        // Calculate last service days
        $lastService = $services->where('status', Service::STATUS_COMPLETED)
            ->whereNotNull('completed_at')
            ->sortByDesc('completed_at')
            ->first();

        $lastServiceDays = null;
        $lastServiceDate = null;
        $lastServiceDateJalali = null;
        $lastServiceDaysText = null;
        $lastServiceDateJalaliWithMonth = null;
        
        if ($lastService && $lastService->completed_at) {
            $lastServiceDate = $lastService->completed_at->toIso8601String();
            $lastServiceJalali = Jalalian::forge($lastService->completed_at);
            $lastServiceDateJalali = $lastServiceJalali->format('Y/m/d');
            
            $today = Carbon::today();
            $lastServiceCarbon = Carbon::parse($lastService->completed_at)->startOfDay();
            // Calculate days passed from last service (positive number = days ago)
            $diffDays = $lastServiceCarbon->diffInDays($today, false);
            
            // Format the days text
            if ($diffDays === 0) {
                $lastServiceDaysText = 'امروز';
            } elseif ($diffDays === 1) {
                $lastServiceDaysText = 'دیروز';
            } elseif ($diffDays > 1) {
                $lastServiceDaysText = $diffDays . ' روز پیش';
            } else {
                // Future date (shouldn't happen, but handle it)
                $lastServiceDaysText = abs($diffDays) . ' روز بعد';
            }
            
            // Store absolute value for days passed
            $lastServiceDays = abs($diffDays);
            
            // Add month name to the date
            $monthNames = [
                1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
                5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
                9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند'
            ];
            $lastServiceMonthName = $monthNames[$lastServiceJalali->getMonth()] ?? $lastServiceJalali->getMonth();
            $lastServiceDateJalaliWithMonth = $lastServiceMonthName . ' ' . $lastServiceJalali->getDay() . '، ' . $lastServiceJalali->getYear();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'building' => $building,
                'statistics' => [
                    'total_services' => $totalServices,
                    'completed_services' => $completedServices,
                    'pending_services' => $pendingServices,
                    'assigned_services' => $assignedServices,
                    'expired_services' => $expiredServices,
                    'cancelled_services' => $cancelledServices,
                ],
                'last_service' => $lastService ? [
                    'id' => $lastService->id,
                    'service_date_text' => ($monthNames[$lastService->service_month] ?? $lastService->service_month) . ' ' . $lastService->service_year,
                    'completed_at' => $lastServiceDate,
                    'completed_at_jalali' => $lastServiceDateJalali,
                    'completed_at_jalali_with_month' => $lastServiceDateJalaliWithMonth,
                    'days_since' => $lastServiceDays,
                    'days_since_text' => $lastServiceDaysText,
                ] : null,
                'services' => $formattedServices,
            ]
        ]);
    }
}
