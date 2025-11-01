<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Province;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status === 'true' || $request->status === true);
        }

        $buildings = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Add Jalali formatted dates
        $items = collect($buildings->items())->map(function ($building) {
            if ($building->service_start_date) {
                $building->service_start_date_jalali = Jalalian::forge($building->service_start_date)->format('Y/m/d H:i:s');
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
            'service_start_date' => 'nullable|string',
            'status' => 'required|in:true,false',
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

        // Convert Jalali date to Gregorian
        if (!empty($data['service_start_date'])) {
            try {
                // Try with time format first
                try {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d H:i:s', $data['service_start_date']);
                } catch (\Exception $e) {
                    // If that fails, try without time
                    $jalaliDate = Jalalian::fromFormat('Y/m/d H:i:s', $data['service_start_date']);
                }
                $data['service_start_date'] = $jalaliDate->toCarbon()->format('Y-m-d H:i:s');
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

        $building = Building::create($data);
        $building = $building->load(['province', 'city', 'organizationUser']);
        
        // Add Jalali formatted date
        if ($building->service_start_date) {
            $building->service_start_date_jalali = Jalalian::forge($building->service_start_date)->format('Y/m/d H:i:s');
        }

        return response()->json([
            'success' => true,
            'message' => 'ساختمان/پروژه با موفقیت ایجاد شد',
            'data' => $building
        ], 201);
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

        $building = Building::with(['province', 'city', 'organizationUser'])
            ->where('organization_id', $user->organization_id)
            ->findOrFail($id);

        // Add Jalali formatted date
        if ($building->service_start_date) {
            $building->service_start_date_jalali = Jalalian::forge($building->service_start_date)->format('Y/m/d H:i:s');
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
            'service_start_date' => 'nullable|string',
            'status' => 'required|in:true,false',
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

        // Convert Jalali date to Gregorian
        if (!empty($data['service_start_date'])) {
            try {
                // Try with time format first
                try {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d H:i:s', $data['service_start_date']);
                } catch (\Exception $e) {
                    // If that fails, try without time
                    $jalaliDate = Jalalian::fromFormat('Y/m/d H:i:s', $data['service_start_date']);
                }
                $data['service_start_date'] = $jalaliDate->toCarbon()->format('Y-m-d H:i:s');
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

        $building->update($data);
        $building = $building->load(['province', 'city', 'organizationUser']);
        
        // Add Jalali formatted date
        if ($building->service_start_date) {
            $building->service_start_date_jalali = Jalalian::forge($building->service_start_date)->format('Y/m/d H:i:s');
        }

        return response()->json([
            'success' => true,
            'message' => 'ساختمان/پروژه با موفقیت به‌روزرسانی شد',
            'data' => $building
        ]);
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
