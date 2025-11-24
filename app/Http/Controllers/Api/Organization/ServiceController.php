<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Building;
use App\Models\Technician;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class ServiceController extends Controller
{
    /**
     * Check if a service is locked (building support period has ended)
     * A service is locked if the service month/year is >= building's service_end_date
     * Manual services (is_manual = true) should not be locked
     */
    private function isServiceLocked($service)
    {
        // Manual services should not be locked
        if ($service->is_manual) {
            return false;
        }

        if (!$service->building || !$service->building->service_end_date) {
            return false;
        }

        $endDateJalali = Jalalian::forge($service->building->service_end_date);
        $endYear = $endDateJalali->getYear();
        $endMonth = $endDateJalali->getMonth();

        // Check if service month/year is >= end date month/year
        if ($service->service_year > $endYear) {
            return true;
        } elseif ($service->service_year == $endYear && $service->service_month >= $endMonth) {
            return true;
        }

        return false;
    }

    /**
     * Generate missing services for buildings
     * Generates one service per month for each building
     * Checks existing services and their statuses
     * service_end_date is only checked to prevent generation after contract ends (but allows generation for the end month)
     */
    private function generateMissingServices($organizationId)
    {
        // Get all active buildings
        $buildings = Building::where('organization_id', $organizationId)
            ->where('status', true)
            ->get();

        $currentJalali = Jalalian::now();
        $currentYear = $currentJalali->getYear();
        $currentMonth = $currentJalali->getMonth();

        // FIRST: Mark expired services for ALL buildings in the organization at once
        // This must happen BEFORE generating new services
        // Expire services where service month/year is BEFORE current month/year
        // NOTE: Only expire system-generated services (is_manual = false), not user-created ones
        // When expiring, also remove technician assignment
        Service::whereHas('building', function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            })
            ->whereIn('status', [Service::STATUS_PENDING, Service::STATUS_ASSIGNED])
            ->where('is_manual', false) // Only expire system-generated services
            ->where(function ($query) use ($currentYear, $currentMonth) {
                // Services from previous years
                $query->where('service_year', '<', $currentYear)
                    // OR services from current year but previous months
                    ->orWhere(function ($q) use ($currentYear, $currentMonth) {
                        $q->where('service_year', $currentYear)
                          ->where('service_month', '<', $currentMonth);
                    });
            })
            ->update([
                'status' => Service::STATUS_EXPIRED,
                'technician_id' => null,
                'assigned_at' => null,
            ]);

        foreach ($buildings as $building) {
            try {

                // Get all existing services for this building (including expired and cancelled for checking, but excluding for latest calculation)
                // We need to check ALL services (including expired and cancelled) to see if a month already has a service
                // But we only use non-expired and non-cancelled services to determine the latest service
                $allServices = Service::where('building_id', $building->id)
                    ->orderBy('service_year', 'asc')
                    ->orderBy('service_month', 'asc')
                    ->get();

                // Get non-expired and non-cancelled services to find the latest
                $existingServices = $allServices->whereNotIn('status', [Service::STATUS_EXPIRED, Service::STATUS_CANCELLED])->values();

                // Check service_end_date - if contract has ended, don't generate beyond that month
                $endYear = null;
                $endMonth = null;
                if ($building->service_end_date) {
                    $endDateJalali = Jalalian::forge($building->service_end_date);
                    $endYear = $endDateJalali->getYear();
                    $endMonth = $endDateJalali->getMonth();
                    // Allow generation for the end month itself
                }

                // Only generate service for the CURRENT month (not all missing months)
                // This ensures services are created once per month when that month arrives
                $currentMonthService = Service::where('building_id', $building->id)
                    ->where('service_month', $currentMonth)
                    ->where('service_year', $currentYear)
                    ->first();

                if (!$currentMonthService) {
                    // Check if we should generate (not past end date)
                    $shouldGenerate = true;
                    if ($endYear !== null && $endMonth !== null) {
                        if ($currentYear > $endYear || ($currentYear == $endYear && $currentMonth > $endMonth)) {
                            $shouldGenerate = false;
                        }
                    }

                    if ($shouldGenerate) {
                        // Only create service for current month
                        Service::create([
                            'building_id' => $building->id,
                            'service_month' => $currentMonth,
                            'service_year' => $currentYear,
                            'status' => Service::STATUS_PENDING,
                            'is_manual' => false,
                        ]);
                    }
                } else if ($currentMonthService->status === Service::STATUS_EXPIRED && $currentMonthService->is_manual == false) {
                    // If current month service exists but is expired (and system-generated), reactivate it
                    // Note: Cancelled services and manual services should NOT be reactivated
                    $currentMonthService->update(['status' => Service::STATUS_PENDING]);
                }
                // If current month service is cancelled or manual, do nothing - leave it as is
            } catch (\Exception $e) {
                // Skip building if there's an error
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

        // Generate missing services before fetching (this also expires old services)
        $this->generateMissingServices($organizationId);

        $currentJalali = Jalalian::now();
        $currentYear = $currentJalali->getYear();
        $currentMonth = $currentJalali->getMonth();

        $query = Service::with(['building.province', 'building.city', 'building.elevators'])
            ->whereHas('building', function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            })
            ->pending();

        // Filter by building
        if ($request->has('building_id') && $request->building_id) {
            $query->where('building_id', $request->building_id);
        }

        // Filter by technician
        if ($request->has('technician_id') && $request->technician_id) {
            $query->where('technician_id', $request->technician_id);
        }

        // Filter by month
        if ($request->has('month') && $request->month) {
            $query->where('service_month', $request->month);
        } else {
            // Only show current month pending services if no month filter
            $query->where('service_month', $currentMonth);
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->where('service_year', $request->year);
        } else {
            // Only show current year pending services if no year filter
            $query->where('service_year', $currentYear);
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
            
            // Check if service is locked (only for pending services)
            // Lock if service month/year >= building service_end_date
            if ($service->status === Service::STATUS_PENDING) {
                $service->is_locked = $this->isServiceLocked($service);
            } else {
                $service->is_locked = false;
            }
            
            // Get last completed service for this building
            $lastService = Service::where('building_id', $service->building_id)
                ->where('status', Service::STATUS_COMPLETED)
                ->whereNotNull('completed_at')
                ->orderBy('completed_at', 'desc')
                ->first();
            
            if ($lastService && $lastService->completed_at) {
                $daysAgo = Carbon::now()->diffInDays($lastService->completed_at, false);
                $service->last_service_days_ago = abs($daysAgo);
                $service->last_service_completed_at = $lastService->completed_at;
                $service->last_service_id = $lastService->id;
            } else {
                $service->last_service_days_ago = null;
                $service->last_service_completed_at = null;
                $service->last_service_id = null;
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

        // Filter by building
        if ($request->has('building_id') && $request->building_id) {
            $query->where('building_id', $request->building_id);
        }

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
            if ($service->visit_date) {
                $service->visit_date_jalali = Jalalian::forge($service->visit_date)->format('Y/m/d');
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
            'visit_date' => 'required|string',
            'visit_time_range' => 'required|string|max:20',
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

        // Convert Jalali date to Gregorian for visit_date
        $visitDate = null;
        if (!empty($request->visit_date)) {
            try {
                // Try parsing with different formats
                if (strpos($request->visit_date, '/') !== false) {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $request->visit_date);
                } else {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $request->visit_date);
                }
                $visitDate = $jalaliDate->toCarbon()->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format for visit_date',
                    'errors' => ['visit_date' => ['فرمت تاریخ نامعتبر است']]
                ], 422);
            }
        }

        $updateData = [
            'technician_id' => $request->technician_id,
            'status' => Service::STATUS_ASSIGNED,
            'assigned_at' => now(),
            'organization_note' => $request->organization_note,
            'visit_date' => $visitDate,
            'visit_time_range' => $request->visit_time_range,
        ];

        $service->update($updateData);

        // Create automatic message to technician
        $service->load(['building']);
        $monthNames = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
            5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
            9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
        ];
        $monthName = $monthNames[$service->service_month] ?? $service->service_month;
        
        Message::create([
            'sender_type' => Message::SENDER_TYPE_ORGANIZATION,
            'sender_id' => $user->organization_id,
            'receiver_type' => Message::RECEIVER_TYPE_TECHNICIAN,
            'receiver_id' => $request->technician_id,
            'subject' => 'اختصاص سرویس جدید',
            'message' => "یک سرویس جدید به شما اختصاص داده شد.\n\nساختمان: {$service->building->name}\nماه: {$monthName} {$service->service_year}\n" . ($request->organization_note ? "یادداشت: {$request->organization_note}" : ''),
            'service_id' => $service->id,
        ]);

        $service->load(['building.province', 'building.city', 'building.elevators', 'technician']);
        $service->status_text = $service->status_text;
        $service->status_badge_class = $service->status_badge_class;
        $service->service_date_text = $service->service_date_text;
        if ($service->assigned_at) {
            $service->assigned_at_jalali = Jalalian::forge($service->assigned_at)->format('Y/m/d H:i:s');
        }
        if ($service->visit_date) {
            $service->visit_date_jalali = Jalalian::forge($service->visit_date)->format('Y/m/d');
        }

        return response()->json([
            'success' => true,
            'message' => 'تکنسین با موفقیت اختصاص داده شد.',
            'data' => $service
        ]);
    }

    /**
     * Change technician for an assigned service
     */
    public function changeTechnician(Request $request, $id)
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

        // Check if service is assigned
        if ($service->status !== Service::STATUS_ASSIGNED) {
            return response()->json([
                'success' => false,
                'message' => 'فقط سرویس‌های اختصاص داده شده را می‌توان تغییر داد.'
            ], 400);
        }

        // Verify technician belongs to same organization
        $technician = Technician::where('organization_id', $user->organization_id)
            ->findOrFail($request->technician_id);

        // Convert Jalali date to Gregorian for visit_date (required)
        $visitDate = null;
        if (!empty($request->visit_date)) {
            try {
                // Try parsing with different formats
                if (strpos($request->visit_date, '/') !== false) {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $request->visit_date);
                } else {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $request->visit_date);
                }
                $visitDate = $jalaliDate->toCarbon()->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format for visit_date',
                    'errors' => ['visit_date' => ['فرمت تاریخ نامعتبر است']]
                ], 422);
            }
        }

        // Update technician
        $oldTechnicianId = $service->technician_id;
        $service->update([
            'technician_id' => $request->technician_id,
            'assigned_at' => now(), // Update assignment time
            'organization_note' => $request->organization_note ?? $service->organization_note,
            'visit_date' => $visitDate,
            'visit_time_range' => $request->visit_time_range,
        ]);

        // Create automatic message to new technician about change
        $service->load(['building']);
        $monthNames = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
            5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
            9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
        ];
        $monthName = $monthNames[$service->service_month] ?? $service->service_month;
        
        Message::create([
            'sender_type' => Message::SENDER_TYPE_ORGANIZATION,
            'sender_id' => $user->organization_id,
            'receiver_type' => Message::RECEIVER_TYPE_TECHNICIAN,
            'receiver_id' => $request->technician_id,
            'subject' => 'تغییر تکنسین سرویس',
            'message' => "تکنسین یک سرویس تغییر کرد و به شما اختصاص داده شد.\n\nساختمان: {$service->building->name}\nماه: {$monthName} {$service->service_year}\n" . ($request->organization_note ? "یادداشت: {$request->organization_note}" : ''),
            'service_id' => $service->id,
        ]);

        $service->load(['building.province', 'building.city', 'building.elevators', 'technician']);
        $service->status_text = $service->status_text;
        $service->status_badge_class = $service->status_badge_class;
        $service->service_date_text = $service->service_date_text;
        if ($service->assigned_at) {
            $service->assigned_at_jalali = Jalalian::forge($service->assigned_at)->format('Y/m/d H:i:s');
        }
        if ($service->visit_date) {
            $service->visit_date_jalali = Jalalian::forge($service->visit_date)->format('Y/m/d');
        }

        return response()->json([
            'success' => true,
            'message' => 'تکنسین با موفقیت تغییر یافت.',
            'data' => $service
        ]);
    }

    /**
     * Update visit date and time range for an assigned service
     */
    public function updateVisit(Request $request, $id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'visit_date' => 'required|string',
            'visit_time_range' => 'required|string|max:20',
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

        // Check if service is assigned
        if ($service->status !== Service::STATUS_ASSIGNED) {
            return response()->json([
                'success' => false,
                'message' => 'فقط سرویس‌های اختصاص داده شده را می‌توان ویرایش کرد.'
            ], 400);
        }

        // Convert Jalali date to Gregorian for visit_date
        $visitDate = null;
        if (!empty($request->visit_date)) {
            try {
                if (strpos($request->visit_date, '/') !== false) {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $request->visit_date);
                } else {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $request->visit_date);
                }
                $visitDate = $jalaliDate->toCarbon()->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format for visit_date',
                    'errors' => ['visit_date' => ['فرمت تاریخ نامعتبر است']]
                ], 422);
            }
        }

        // Update visit date and time range
        $service->update([
            'visit_date' => $visitDate,
            'visit_time_range' => $request->visit_time_range,
        ]);

        $service->load(['building.province', 'building.city', 'building.elevators', 'technician']);
        $service->status_text = $service->status_text;
        $service->status_badge_class = $service->status_badge_class;
        $service->service_date_text = $service->service_date_text;
        if ($service->assigned_at) {
            $service->assigned_at_jalali = Jalalian::forge($service->assigned_at)->format('Y/m/d H:i:s');
        }
        if ($service->visit_date) {
            $service->visit_date_jalali = Jalalian::forge($service->visit_date)->format('Y/m/d');
        }

        return response()->json([
            'success' => true,
            'message' => 'تاریخ و بازه زمانی مراجعه با موفقیت به‌روزرسانی شد.',
            'data' => $service
        ]);
    }

    /**
     * Cancel service (remove technician and set status to pending)
     */
    public function cancelService($id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $service = Service::with(['building'])
            ->whereHas('building', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            })
            ->findOrFail($id);

        // Check if service is completed - cannot cancel completed services
        if ($service->status === Service::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'سرویس‌های تکمیل شده را نمی‌توان لغو کرد.'
            ], 400);
        }

        // Check if service is already cancelled
        if ($service->status === Service::STATUS_CANCELLED) {
            return response()->json([
                'success' => false,
                'message' => 'این سرویس قبلاً لغو شده است.'
            ], 400);
        }

        // Remove technician and set status to cancelled
        // Can cancel pending, assigned, or expired services
        $service->update([
            'technician_id' => null,
            'status' => Service::STATUS_CANCELLED,
            'assigned_at' => null,
        ]);

        $service->load(['building.province', 'building.city', 'building.elevators']);
        $service->status_text = $service->status_text;
        $service->status_badge_class = $service->status_badge_class;
        $service->service_date_text = $service->service_date_text;

        return response()->json([
            'success' => true,
            'message' => 'سرویس با موفقیت لغو شد و تکنسین حذف شد.',
            'data' => $service
        ]);
    }

    /**
     * Revert service (set is_manual to true)
     * This makes the service manual so it won't be locked
     */
    public function revertService($id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $service = Service::with(['building'])
            ->whereHas('building', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            })
            ->findOrFail($id);

        // Set is_manual to true
        $service->update([
            'is_manual' => true,
        ]);

        $service->load(['building.province', 'building.city', 'building.elevators']);
        $service->status_text = $service->status_text;
        $service->status_badge_class = $service->status_badge_class;
        $service->service_date_text = $service->service_date_text;

        return response()->json([
            'success' => true,
            'message' => 'سرویس با موفقیت به حالت دستی تبدیل شد.',
            'data' => $service
        ]);
    }

    /**
     * Cancel building and service
     * Disables the building and cancels the service
     */
    public function cancelBuildingAndService($id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $service = Service::with(['building'])
            ->whereHas('building', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            })
            ->findOrFail($id);

        // Check if service is completed - cannot cancel completed services
        if ($service->status === Service::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'سرویس‌های تکمیل شده را نمی‌توان لغو کرد.'
            ], 400);
        }

        // Disable the building
        $service->building->update([
            'status' => false,
        ]);

        // Cancel the service
        $service->update([
            'technician_id' => null,
            'status' => Service::STATUS_CANCELLED,
            'assigned_at' => null,
        ]);

        $service->load(['building.province', 'building.city', 'building.elevators']);
        $service->status_text = $service->status_text;
        $service->status_badge_class = $service->status_badge_class;
        $service->service_date_text = $service->service_date_text;

        return response()->json([
            'success' => true,
            'message' => 'ساختمان غیرفعال شد و سرویس لغو شد.',
            'data' => $service
        ]);
    }

    /**
     * Get building information for locked service
     */
    public function getBuildingInfo($id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $service = Service::with(['building'])
            ->whereHas('building', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            })
            ->findOrFail($id);

        $building = $service->building;
        
        $data = [
            'building_name' => $building->name,
            'service_start_date' => null,
            'service_start_date_jalali' => null,
            'service_end_date' => null,
            'service_end_date_jalali' => null,
            'completed_services_count' => 0,
            'last_service_date' => null,
            'last_service_date_jalali' => null,
            'last_service_days_ago' => null,
        ];

        // Get service start and end dates
        if ($building->service_start_date) {
            $data['service_start_date'] = $building->service_start_date;
            $data['service_start_date_jalali'] = Jalalian::forge($building->service_start_date)->format('Y/m/d');
        }

        if ($building->service_end_date) {
            $data['service_end_date'] = $building->service_end_date;
            $data['service_end_date_jalali'] = Jalalian::forge($building->service_end_date)->format('Y/m/d');
        }

        // Get completed services count for this building
        $completedCount = Service::where('building_id', $building->id)
            ->where('status', Service::STATUS_COMPLETED)
            ->count();
        $data['completed_services_count'] = $completedCount;

        // Get last completed service
        $lastService = Service::where('building_id', $building->id)
            ->where('status', Service::STATUS_COMPLETED)
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->first();

        if ($lastService && $lastService->completed_at) {
            $data['last_service_date'] = $lastService->completed_at;
            $data['last_service_date_jalali'] = Jalalian::forge($lastService->completed_at)->format('Y/m/d');
            
            // Calculate days ago
            $daysAgo = Carbon::now()->diffInDays($lastService->completed_at, false);
            $data['last_service_days_ago'] = abs($daysAgo);
        }

        return response()->json([
            'success' => true,
            'data' => $data
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

    /**
     * Get completed services
     */
    public function completed(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $organizationId = $user->organization_id;

        // Generate missing services before fetching (in case new buildings were added)
        $this->generateMissingServices($organizationId);

        $query = Service::with([
            'building.province',
            'building.city',
            'building.elevators',
            'technician',
            'checklist.elevatorChecklists.elevator',
            'checklist.elevatorChecklists.descriptions.checklist',
            'checklist.managerSignature',
            'checklist.technicianSignature',
            'checklist.history.technician'
        ])
            ->whereHas('building', function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            })
            ->completed();

        // Filter by building
        if ($request->has('building_id') && $request->building_id) {
            $query->where('building_id', $request->building_id);
        }

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

        $services = $query->orderBy('completed_at', 'desc')
            ->orderBy('service_year', 'desc')
            ->orderBy('service_month', 'desc')
            ->paginate(10);

        // Add formatted data with full details
        $items = collect($services->items())->map(function ($service) {
            $service->status_text = $service->status_text;
            $service->status_badge_class = $service->status_badge_class;
            $service->service_date_text = $service->service_date_text;
            
            // Add assigned information
            if ($service->assigned_at) {
                $service->assigned_at_jalali = Jalalian::forge($service->assigned_at)->format('Y/m/d H:i:s');
            }
            if ($service->visit_date) {
                $service->visit_date_jalali = Jalalian::forge($service->visit_date)->format('Y/m/d');
            }
            
            // Add completed information
            if ($service->completed_at) {
                $service->completed_at_jalali = Jalalian::forge($service->completed_at)->format('Y/m/d H:i:s');
            }
            
            // Add checklist data for completed services
            if ($service->checklist) {
                $checklist = $service->checklist;
                
                // Format elevator checklists
                $service->checklist_data = [
                    'submitted_at' => $checklist->submitted_at ? Jalalian::forge($checklist->submitted_at)->format('Y/m/d H:i:s') : null,
                    'elevators' => $checklist->elevatorChecklists->map(function ($elevatorChecklist) {
                        return [
                            'elevator_id' => $elevatorChecklist->elevator_id,
                            'elevator_name' => $elevatorChecklist->elevator ? $elevatorChecklist->elevator->name : null,
                            'verified' => $elevatorChecklist->verified,
                            'descriptions' => $elevatorChecklist->descriptions->map(function ($desc) {
                                return [
                                    'checklist_id' => $desc->checklist_id,
                                    'checklist_title' => $desc->checklist ? $desc->checklist->title : null,
                                    'title' => $desc->title,
                                    'description' => $desc->description,
                                ];
                            }),
                        ];
                    }),
                    'manager_signature' => $checklist->managerSignature ? [
                        'name' => $checklist->managerSignature->name,
                        'signature' => $checklist->managerSignature->signature, // Base64 image
                    ] : null,
                    'technician_signature' => $checklist->technicianSignature ? [
                        'name' => $checklist->technicianSignature->name,
                        'signature' => $checklist->technicianSignature->signature, // Base64 image
                    ] : null,
                    'history' => $checklist->history->map(function ($history) {
                        return [
                            'action' => $history->action,
                            'technician_name' => $history->technician ? ($history->technician->first_name . ' ' . $history->technician->last_name) : null,
                            'changes' => $history->changes,
                            'notes' => $history->notes,
                            'created_at' => $history->created_at ? Jalalian::forge($history->created_at)->format('Y/m/d H:i:s') : null,
                        ];
                    }),
                ];
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
     * Get all services (pending, assigned, completed, expired) with full details
     */
    public function all(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $organizationId = $user->organization_id;

        // Generate missing services before fetching (this also expires old services)
        $this->generateMissingServices($organizationId);

        $query = Service::with([
            'building.province',
            'building.city',
            'building.elevators',
            'technician',
            'checklist.elevatorChecklists.elevator',
            'checklist.elevatorChecklists.descriptions.checklist',
            'checklist.managerSignature',
            'checklist.technicianSignature',
            'checklist.history.technician'
        ])
            ->whereHas('building', function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            });

        // Filter by building
        if ($request->has('building_id') && $request->building_id) {
            $query->where('building_id', $request->building_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

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

        $services = $query->orderBy('service_year', 'desc')
            ->orderBy('service_month', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Add formatted data with full details
        $items = collect($services->items())->map(function ($service) {
            $service->status_text = $service->status_text;
            $service->status_badge_class = $service->status_badge_class;
            $service->service_date_text = $service->service_date_text;
            
            // Check if service is locked (only for pending services)
            // Lock if service month/year >= building service_end_date
            if ($service->status === Service::STATUS_PENDING) {
                $service->is_locked = $this->isServiceLocked($service);
            } else {
                $service->is_locked = false;
            }
            
            // Add assigned information
            if ($service->assigned_at) {
                $service->assigned_at_jalali = Jalalian::forge($service->assigned_at)->format('Y/m/d H:i:s');
            }
            if ($service->visit_date) {
                $service->visit_date_jalali = Jalalian::forge($service->visit_date)->format('Y/m/d');
            }
            
            // Add completed information
            if ($service->completed_at) {
                $service->completed_at_jalali = Jalalian::forge($service->completed_at)->format('Y/m/d H:i:s');
            }
            
            // Add checklist data for completed services
            if ($service->status === Service::STATUS_COMPLETED && $service->checklist) {
                $checklist = $service->checklist;
                
                // Format elevator checklists
                $service->checklist_data = [
                    'submitted_at' => $checklist->submitted_at ? Jalalian::forge($checklist->submitted_at)->format('Y/m/d H:i:s') : null,
                    'elevators' => $checklist->elevatorChecklists->map(function ($elevatorChecklist) {
                        return [
                            'elevator_id' => $elevatorChecklist->elevator_id,
                            'elevator_name' => $elevatorChecklist->elevator ? $elevatorChecklist->elevator->name : null,
                            'verified' => $elevatorChecklist->verified,
                            'descriptions' => $elevatorChecklist->descriptions->map(function ($desc) {
                                return [
                                    'checklist_id' => $desc->checklist_id,
                                    'checklist_title' => $desc->checklist ? $desc->checklist->title : null,
                                    'title' => $desc->title,
                                    'description' => $desc->description,
                                ];
                            }),
                        ];
                    }),
                    'manager_signature' => $checklist->managerSignature ? [
                        'name' => $checklist->managerSignature->name,
                        'signature' => $checklist->managerSignature->signature, // Base64 image
                    ] : null,
                    'technician_signature' => $checklist->technicianSignature ? [
                        'name' => $checklist->technicianSignature->name,
                        'signature' => $checklist->technicianSignature->signature, // Base64 image
                    ] : null,
                    'history' => $checklist->history->map(function ($history) {
                        return [
                            'action' => $history->action,
                            'technician_name' => $history->technician ? ($history->technician->first_name . ' ' . $history->technician->last_name) : null,
                            'changes' => $history->changes,
                            'notes' => $history->notes,
                            'created_at' => $history->created_at ? Jalalian::forge($history->created_at)->format('Y/m/d H:i:s') : null,
                        ];
                    }),
                ];
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
     * Create a new service manually
     */
    public function store(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'building_id' => 'required|exists:buildings,id',
            'service_month' => 'required|integer|min:1|max:12',
            'service_year' => 'required|integer|min:1400|max:1500',
        ], [
            'building_id.required' => 'انتخاب ساختمان الزامی است',
            'building_id.exists' => 'ساختمان انتخاب شده معتبر نیست',
            'service_month.required' => 'انتخاب ماه الزامی است',
            'service_month.integer' => 'ماه باید عدد باشد',
            'service_month.min' => 'ماه باید بین 1 تا 12 باشد',
            'service_month.max' => 'ماه باید بین 1 تا 12 باشد',
            'service_year.required' => 'انتخاب سال الزامی است',
            'service_year.integer' => 'سال باید عدد باشد',
            'service_year.min' => 'سال باید معتبر باشد',
            'service_year.max' => 'سال باید معتبر باشد',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $buildingId = $request->building_id;
        $serviceMonth = (int) $request->service_month;
        $serviceYear = (int) $request->service_year;

        // Verify building belongs to the organization
        $building = Building::where('id', $buildingId)
            ->where('organization_id', $user->organization_id)
            ->firstOrFail();

        // Create the service (user-created, so is_manual = true)
        // Multiple services can now be created for the same building/month/year
        $service = Service::create([
            'building_id' => $buildingId,
            'service_month' => $serviceMonth,
            'service_year' => $serviceYear,
            'status' => Service::STATUS_PENDING,
            'is_manual' => true, // Mark as user-created to prevent automatic expiration
        ]);

        $service->load(['building.province', 'building.city', 'building.elevators']);
        $service->status_text = $service->status_text;
        $service->status_badge_class = $service->status_badge_class;
        $service->service_date_text = $service->service_date_text;

        return response()->json([
            'success' => true,
            'message' => 'سرویس با موفقیت ایجاد شد.',
            'data' => $service
        ], 201);
    }
}
