<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Elevator;
use App\Models\Province;
use App\Models\City;
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

        // Filter expiring contracts (service_end_date within next N days, default 30)
        if ($request->has('expiring') && $request->expiring === 'true') {
            $today = Carbon::today();
            $days = $request->has('days') ? (int)$request->days : 30;
            $endDate = Carbon::today()->addDays($days);
            $query->whereNotNull('service_end_date')
                  ->whereBetween('service_end_date', [$today, $endDate])
                  ->where('service_end_date', '>=', $today) // Only future dates
                  ->orderBy('service_end_date', 'asc');
        } 
        // Filter expired contracts (service_end_date is in the past)
        elseif ($request->has('expired') && $request->expired === 'true') {
            $today = Carbon::today();
            $query->whereNotNull('service_end_date')
                  ->where('service_end_date', '<', $today)
                  ->orderBy('service_end_date', 'desc');
        } 
        else {
            $query->orderBy('created_at', 'desc');
        }

        $buildings = $query->paginate(10);
        
        $today = Carbon::today();
        
        // Add Jalali formatted dates and calculate days difference
        $items = collect($buildings->items())->map(function ($building) use ($today) {
            if ($building->service_start_date) {
                $building->service_start_date_jalali = Jalalian::forge($building->service_start_date)->format('Y/m/d');
            }
            if ($building->service_end_date) {
                $building->service_end_date_jalali = Jalalian::forge($building->service_end_date)->format('Y/m/d');
                
                // Calculate days difference
                $endDate = Carbon::parse($building->service_end_date);
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
            'service_start_date' => 'required|string',
            'service_end_date' => 'required|string',
            'status' => 'required|in:true,false',
            'elevators_count' => 'nullable|integer|min:0',
            'monthly_amount' => 'nullable|numeric|min:0',
            'elevators' => 'required|array|min:1',
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
        $data['organization_id'] = $user->organization_id;
        $data['organization_user_id'] = $user->id;
        $data['status'] = $data['status'] === 'true' || $data['status'] === true;
        $data['elevators_count'] = $data['elevators_count'] ?? 0;

        // Convert Jalali date to Gregorian
        if (!empty($data['service_start_date'])) {
            try {
                // Try with time format first
                try {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $data['service_start_date']);
                } catch (\Exception $e) {
                    // If that fails, try without time
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $data['service_start_date']);
                }
                $data['service_start_date'] = $jalaliDate->toCarbon()->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format for service_start_date',
                    'errors' => ['service_start_date' => ['فرمت تاریخ نامعتبر است']]
                ], 422);
            }
        } else {
            unset($data['service_start_date']);
        }

        // Convert Jalali date to Gregorian for service_end_date
        if (!empty($data['service_end_date'])) {
            try {
                // Try with time format first
                try {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $data['service_end_date']);
                } catch (\Exception $e) {
                    // If that fails, try without time
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $data['service_end_date']);
                }
                $data['service_end_date'] = $jalaliDate->toCarbon()->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format for service_end_date',
                    'errors' => ['service_end_date' => ['فرمت تاریخ نامعتبر است']]
                ], 422);
            }
        } else {
            unset($data['service_end_date']);
        }

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

        // Add Jalali formatted dates
        if ($building->service_start_date) {
            $building->service_start_date_jalali = Jalalian::forge($building->service_start_date)->format('Y/m/d');
        }else{
            $building->service_start_date_jalali = null;
        }
        if ($building->service_end_date) {
            $building->service_end_date_jalali = Jalalian::forge($building->service_end_date)->format('Y/m/d');
        }else{
            $building->service_end_date_jalali = null;
        }

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
            'service_start_date' => 'required|string',
            'service_end_date' => 'required|string',
            'status' => 'required|in:true,false',
            'elevators_count' => 'nullable|integer|min:0',
            'monthly_amount' => 'nullable|numeric|min:0',
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

        // Convert Jalali date to Gregorian
        if (!empty($data['service_start_date'])) {
            try {
                // Try with time format first
                try {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $data['service_start_date']);
                } catch (\Exception $e) {
                    // If that fails, try without time
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $data['service_start_date']);
                }
                $data['service_start_date'] = $jalaliDate->toCarbon()->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format for service_start_date',
                    'errors' => ['service_start_date' => ['فرمت تاریخ نامعتبر است']]
                ], 422);
            }
        } else {
            // If empty, set to null to allow clearing the date
            $data['service_start_date'] = null;
        }

        // Convert Jalali date to Gregorian for service_end_date
        if (!empty($data['service_end_date'])) {
            try {
                // Try with time format first
                try {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $data['service_end_date']);
                } catch (\Exception $e) {
                    // If that fails, try without time
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $data['service_end_date']);
                }
                $data['service_end_date'] = $jalaliDate->toCarbon()->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format for service_end_date',
                    'errors' => ['service_end_date' => ['فرمت تاریخ نامعتبر است']]
                ], 422);
            }
        } else {
            // If empty, set to null to allow clearing the date
            $data['service_end_date'] = null;
        }

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
}
