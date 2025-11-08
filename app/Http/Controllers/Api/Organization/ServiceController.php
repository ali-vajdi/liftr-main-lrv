<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Building;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class ServiceController extends Controller
{
    /**
     * Generate missing services for buildings
     */
    private function generateMissingServices($organizationId)
    {
        // Get all active buildings with service_start_date
        $buildings = Building::where('organization_id', $organizationId)
            ->where('status', true)
            ->get();

        $currentJalali = Jalalian::now();
        $currentYear = $currentJalali->getYear();
        $currentMonth = $currentJalali->getMonth();

        foreach ($buildings as $building) {
            try {
                // Convert service_start_date to Jalali
                $startDate = Jalalian::forge($building->service_start_date);
                $startYear = $startDate->getYear();
                $startMonth = $startDate->getMonth();

                // Generate services from start date to current month
                $year = $startYear;
                $month = $startMonth;

                while ($year < $currentYear || ($year == $currentYear && $month <= $currentMonth)) {
                    // Check if service already exists
                    $existingService = Service::where('building_id', $building->id)
                        ->where('service_month', $month)
                        ->where('service_year', $year)
                        ->first();

                    if (!$existingService) {
                        // Create new service
                        Service::create([
                            'building_id' => $building->id,
                            'service_month' => $month,
                            'service_year' => $year,
                            'status' => Service::STATUS_PENDING,
                        ]);
                    }

                    // Move to next month
                    $month++;
                    if ($month > 12) {
                        $month = 1;
                        $year++;
                    }
                }
            } catch (\Exception $e) {
                // Skip building if there's an error with date conversion
                Log::warning("Error generating services for building {$building->id}: " . $e->getMessage());
                continue;
            }
        }
    }

    /**
     * Get pending services
     */
    public function pending(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $organizationId = $user->organization_id;

        // Generate missing services before fetching
        $this->generateMissingServices($organizationId);

        $query = Service::with(['building.province', 'building.city', 'building.elevators'])
            ->whereHas('building', function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            })
            ->pending();

        // Filter by month
        if ($request->has('month') && $request->month) {
            $query->where('service_month', $request->month);
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->where('service_year', $request->year);
        }

        // Search by building name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('building', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%");
            });
        }

        $services = $query->orderBy('service_year', 'desc')
            ->orderBy('service_month', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Add formatted data
        $items = collect($services->items())->map(function ($service) {
            $service->status_text = $service->status_text;
            $service->status_badge_class = $service->status_badge_class;
            $service->service_date_text = $service->service_date_text;
            return $service;
        });

        return response()->json([
            'success' => true,
            'data' => $items->all(),
            'pagination' => [
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
            ]
        ]);
    }

    /**
     * Get assigned services
     */
    public function assigned(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $organizationId = $user->organization_id;

        // Generate missing services before fetching (in case new buildings were added)
        $this->generateMissingServices($organizationId);

        $query = Service::with(['building.province', 'building.city', 'building.elevators', 'technician'])
            ->whereHas('building', function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            })
            ->assigned();

        // Filter by technician
        if ($request->has('technician_id') && $request->technician_id) {
            $query->where('technician_id', $request->technician_id);
        }

        // Filter by month
        if ($request->has('month') && $request->month) {
            $query->where('service_month', $request->month);
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->where('service_year', $request->year);
        }

        // Search by building name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('building', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%");
            });
        }

        $services = $query->orderBy('assigned_at', 'desc')
            ->orderBy('service_year', 'desc')
            ->orderBy('service_month', 'desc')
            ->paginate(10);

        // Add formatted data
        $items = collect($services->items())->map(function ($service) {
            $service->status_text = $service->status_text;
            $service->status_badge_class = $service->status_badge_class;
            $service->service_date_text = $service->service_date_text;
            if ($service->assigned_at) {
                $service->assigned_at_jalali = Jalalian::forge($service->assigned_at)->format('Y/m/d H:i:s');
            }
            return $service;
        });

        return response()->json([
            'success' => true,
            'data' => $items->all(),
            'pagination' => [
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
            ]
        ]);
    }

    /**
     * Assign technician to a service
     */
    public function assignTechnician(Request $request, $id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'technician_id' => 'required|exists:technicians,id',
            'organization_note' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $service = Service::with(['building'])
            ->whereHas('building', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            })
            ->findOrFail($id);

        // Check if service is pending
        if ($service->status !== Service::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'این سرویس قبلاً اختصاص داده شده است.'
            ], 400);
        }

        // Verify technician belongs to same organization
        $technician = Technician::where('organization_id', $user->organization_id)
            ->findOrFail($request->technician_id);

        $service->update([
            'technician_id' => $request->technician_id,
            'status' => Service::STATUS_ASSIGNED,
            'assigned_at' => now(),
            'organization_note' => $request->organization_note,
        ]);

        $service->load(['building.province', 'building.city', 'building.elevators', 'technician']);
        $service->status_text = $service->status_text;
        $service->status_badge_class = $service->status_badge_class;
        $service->service_date_text = $service->service_date_text;
        if ($service->assigned_at) {
            $service->assigned_at_jalali = Jalalian::forge($service->assigned_at)->format('Y/m/d H:i:s');
        }

        return response()->json([
            'success' => true,
            'message' => 'تکنسین با موفقیت اختصاص داده شد.',
            'data' => $service
        ]);
    }

    /**
     * Get available technicians for assignment
     */
    public function getTechnicians()
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $technicians = Technician::where('organization_id', $user->organization_id)
            ->where('status', true)
            ->select('id', 'first_name', 'last_name', 'phone_number')
            ->get()
            ->map(function ($tech) {
                return [
                    'id' => $tech->id,
                    'name' => trim($tech->first_name . ' ' . $tech->last_name),
                    'phone_number' => $tech->phone_number,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $technicians
        ]);
    }
}
