<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceView;
use App\Models\Building;
use App\Models\BuildingContract;
use App\Models\BuildingFinancialRecord;
use App\Models\PaymentPeriod;
use App\Models\Technician;
use App\Models\Message;
use App\Models\Organization;
use App\Models\PdfVerificationCode;
use App\Models\UnitChecklist;
use App\Services\SmsService;
use App\Services\SmsPattern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class ServiceController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }
    /**
     * Check if a service is locked (contract period has ended or contract is cancelled/finished)
     * A service is locked if:
     * - Contract is cancelled or finished
     * - Service month/year is >= contract's contract_end_date (only for active contracts)
     * Manual services (is_manual = true) should not be locked
     */
    private function isServiceLocked($service)
    {
        // Manual services should not be locked
        if ($service->is_manual) {
            return false;
        }

        // If service has a contract, check contract status
        if ($service->buildingContract) {
            // If contract is cancelled or finished, services are locked
            if (in_array($service->buildingContract->status, [
                BuildingContract::STATUS_CANCELLED,
                BuildingContract::STATUS_FINISHED
            ])) {
                return true;
            }

            // For active contracts, check if service month/year is >= contract end date
            if ($service->buildingContract->status === BuildingContract::STATUS_ACTIVE 
                && $service->buildingContract->contract_end_date) {
                $endDateJalali = Jalalian::forge($service->buildingContract->contract_end_date);
                $endYear = $endDateJalali->getYear();
                $endMonth = $endDateJalali->getMonth();

                // Check if service month/year is >= end date month/year
                if ($service->service_year > $endYear) {
                    return true;
                } elseif ($service->service_year == $endYear && $service->service_month >= $endMonth) {
                    return true;
                }
            }
        }

        // Fallback to building service_end_date for legacy services without contracts
        if (!$service->buildingContract && $service->building && $service->building->service_end_date) {
            $endDateJalali = Jalalian::forge($service->building->service_end_date);
            $endYear = $endDateJalali->getYear();
            $endMonth = $endDateJalali->getMonth();

            if ($service->service_year > $endYear) {
                return true;
            } elseif ($service->service_year == $endYear && $service->service_month >= $endMonth) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate missing services for buildings
     * DISABLED: Services are now generated automatically from contracts
     * This method is kept for backward compatibility but does nothing
     * Services are now created when contracts are created via BuildingContract::generateServices()
     */
    private function generateMissingServices($organizationId)
    {
        // Services are now generated from contracts, not automatically per building
        // This method is disabled to prevent automatic service generation
        // Only expire old services that don't have a contract
        $currentJalali = Jalalian::now();
        $currentYear = $currentJalali->getYear();
        $currentMonth = $currentJalali->getMonth();

        // Mark expired services for services without contracts (legacy services)
        $expiredServices = Service::whereHas('building', function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            })
            ->whereNull('building_contract_id') // Only expire services without contracts
            ->whereIn('status', [Service::STATUS_PENDING, Service::STATUS_ASSIGNED])
            ->where('is_manual', false)
            ->where(function ($query) use ($currentYear, $currentMonth) {
                $query->where('service_year', '<', $currentYear)
                    ->orWhere(function ($q) use ($currentYear, $currentMonth) {
                        $q->where('service_year', $currentYear)
                          ->where('service_month', '<', $currentMonth);
                    });
            })
            ->get();

        foreach ($expiredServices as $service) {
            $service->update([
                'status' => Service::STATUS_EXPIRED,
                'technician_id' => null,
                'assigned_at' => null,
            ]);
            
            // Create financial record for expired service (only if has contract)
            if ($service->building_contract_id) {
                $this->createFinancialRecordForExpiredOrCancelledService($service, 'expired');
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

        $query = Service::with(['building.province', 'building.city', 'building.elevators', 'buildingContract'])
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
            
            // Add view count and view details
            $views = ServiceView::where('service_id', $service->id)
                ->orderBy('viewed_at', 'desc')
                ->get();
            
            $service->view_count = $views->count();
            $service->views = $views->map(function ($view) {
                return [
                    'id' => $view->id,
                    'ip_address' => $view->ip_address,
                    'device_type' => $view->device_type,
                    'browser' => $view->browser,
                    'platform' => $view->platform,
                    'viewed_at' => $view->viewed_at ? Jalalian::forge($view->viewed_at)->format('Y/m/d H:i:s') : null,
                ];
            });
            
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

        $query = Service::with(['building.province', 'building.city', 'building.elevators', 'buildingContract', 'technician'])
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
            
            // Add view count and view details
            $views = ServiceView::where('service_id', $service->id)
                ->orderBy('viewed_at', 'desc')
                ->get();
            
            $service->view_count = $views->count();
            $service->views = $views->map(function ($view) {
                return [
                    'id' => $view->id,
                    'ip_address' => $view->ip_address,
                    'device_type' => $view->device_type,
                    'browser' => $view->browser,
                    'platform' => $view->platform,
                    'viewed_at' => $view->viewed_at ? Jalalian::forge($view->viewed_at)->format('Y/m/d H:i:s') : null,
                ];
            });
            
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

        $service = Service::with(['building', 'buildingContract'])
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
        
        // Refresh service to ensure slug is available
        $service->refresh();

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

        // Send SMS to building manager
        if ($service->building && $service->building->manager_phone) {
            $organization = Organization::findOrFail($user->organization_id);
            
            // Format date_value as "آذر 1404" (month name + year) using service_month and service_year
            $dateValue = $monthName . ' ' . $service->service_year;
            
            $smsResult = $this->smsService->sendBuildingManagerTechnicianAssignedSms(
                $organization,
                $service->building->manager_phone,
                $service->building->name,
                $dateValue,
                $service->slug,
                true // Use queue
            );

            if (!$smsResult['success']) {
                Log::error('Building manager technician assigned SMS failed', [
                    'service_id' => $service->id,
                    'building_id' => $service->building->id,
                    'phone_number' => $service->building->manager_phone,
                    'error' => $smsResult['error'] ?? 'Unknown error',
                ]);
            }
        }

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

        $service = Service::with(['building', 'buildingContract'])
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

        // Get old technician before updating
        $oldTechnician = null;
        if ($service->technician_id) {
            $oldTechnician = Technician::find($service->technician_id);
        }

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

        // Refresh service to ensure slug is available
        $service->refresh();

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

        // Send SMS to building manager about technician change
        if ($service->building && $service->building->manager_phone && $oldTechnician) {
            $organization = Organization::findOrFail($user->organization_id);
            
            $smsResult = $this->smsService->sendBuildingManagerTechnicianChangedSms(
                $organization,
                $service->building->manager_phone,
                $service->building->name,
                $service->slug,
                true // Use queue
            );

            if (!$smsResult['success']) {
                Log::error('Building manager technician changed SMS failed', [
                    'service_id' => $service->id,
                    'building_id' => $service->building->id,
                    'phone_number' => $service->building->manager_phone,
                    'old_technician_id' => $oldTechnicianId,
                    'new_technician_id' => $request->technician_id,
                    'error' => $smsResult['error'] ?? 'Unknown error',
                ]);
            }
        }

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

        $service = Service::with(['building', 'buildingContract'])
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

        // Refresh service to ensure slug is available
        $service->refresh();

        // Send SMS to building manager about visit date/time update
        if ($service->building && $service->building->manager_phone) {
            $organization = Organization::findOrFail($user->organization_id);
            
            $smsResult = $this->smsService->sendBuildingManagerVisitUpdatedSms(
                $organization,
                $service->building->manager_phone,
                $service->building->name,
                $service->slug,
                true // Use queue
            );

            if (!$smsResult['success']) {
                Log::error('Building manager visit updated SMS failed', [
                    'service_id' => $service->id,
                    'building_id' => $service->building->id,
                    'phone_number' => $service->building->manager_phone,
                    'error' => $smsResult['error'] ?? 'Unknown error',
                ]);
            }
        }

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

        $service = Service::with(['building', 'buildingContract'])
            ->whereHas('building', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            })
            ->findOrFail($id);

        // Check if service is completed - cannot cancel completed services
        if ($service->status === Service::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'سرویس‌های انجام شده را نمی‌توان لغو کرد.'
            ], 400);
        }

        // Check if service is already cancelled
        if ($service->status === Service::STATUS_CANCELLED) {
            return response()->json([
                'success' => false,
                'message' => 'این سرویس قبلاً لغو شده است.'
            ], 400);
        }

        // Store technician ID before removing it (for sending message)
        $technicianId = $service->technician_id;

        // Remove technician and set status to cancelled
        // Can cancel pending, assigned, or expired services
        $service->update([
            'technician_id' => null,
            'status' => Service::STATUS_CANCELLED,
            'assigned_at' => null,
        ]);

        // Reload service with relationships
        $service->load(['buildingContract', 'paymentPeriod']);

        // Create financial record for cancelled service
        if ($service->building_contract_id) {
            $this->createFinancialRecordForExpiredOrCancelledService($service, 'cancelled');
        }

        // Handle financial records based on contract payment timing
        $this->handleServiceFinancialRecord($service);

        // Send message to technician if one was assigned
        if ($technicianId) {
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
                'receiver_id' => $technicianId,
                'subject' => 'لغو سرویس',
                'message' => "یک سرویس که به شما اختصاص داده شده بود لغو شد.\n\nساختمان: {$service->building->name}\nماه: {$monthName} {$service->service_year}",
                'service_id' => $service->id,
            ]);
        }

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

        $service = Service::with(['building', 'buildingContract'])
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

        $service = Service::with(['building', 'buildingContract'])
            ->whereHas('building', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            })
            ->findOrFail($id);

        // Check if service is completed - cannot cancel completed services
        if ($service->status === Service::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'سرویس‌های انجام شده را نمی‌توان لغو کرد.'
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

        $service = Service::with(['building', 'buildingContract'])
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
            
            // Add view count and view details
            $views = ServiceView::where('service_id', $service->id)
                ->orderBy('viewed_at', 'desc')
                ->get();
            
            $service->view_count = $views->count();
            $service->views = $views->map(function ($view) {
                return [
                    'id' => $view->id,
                    'ip_address' => $view->ip_address,
                    'device_type' => $view->device_type,
                    'browser' => $view->browser,
                    'platform' => $view->platform,
                    'viewed_at' => $view->viewed_at ? Jalalian::forge($view->viewed_at)->format('Y/m/d H:i:s') : null,
                ];
            });
            
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
            
            // Add view count and view details
            $views = ServiceView::where('service_id', $service->id)
                ->orderBy('viewed_at', 'desc')
                ->get();
            
            $service->view_count = $views->count();
            $service->views = $views->map(function ($view) {
                return [
                    'id' => $view->id,
                    'ip_address' => $view->ip_address,
                    'device_type' => $view->device_type,
                    'browser' => $view->browser,
                    'platform' => $view->platform,
                    'viewed_at' => $view->viewed_at ? Jalalian::forge($view->viewed_at)->format('Y/m/d H:i:s') : null,
                ];
            });
            
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
            'amount' => 'required|numeric|min:0',
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
            'amount.required' => 'مبلغ سرویس الزامی است',
            'amount.numeric' => 'مبلغ باید عدد باشد',
            'amount.min' => 'مبلغ باید بیشتر از صفر باشد',
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
        $amount = $request->amount;

        // Verify building belongs to the organization
        $building = Building::where('id', $buildingId)
            ->where('organization_id', $user->organization_id)
            ->firstOrFail();

        // Check if building has an active contract
        $activeContract = BuildingContract::where('building_id', $buildingId)
            ->where('status', BuildingContract::STATUS_ACTIVE)
            ->first();

        // Create the service (user-created, so is_manual = true)
        // Multiple services can now be created for the same building/month/year
        $service = Service::create([
            'building_id' => $buildingId,
            'building_contract_id' => $activeContract ? $activeContract->id : null,
            'service_month' => $serviceMonth,
            'service_year' => $serviceYear,
            'monthly_amount' => $amount,
            'status' => Service::STATUS_PENDING,
            'is_manual' => true, // Mark as user-created to prevent automatic expiration
        ]);

        // If service has a contract, regenerate payment periods to link this service
        if ($activeContract) {
            $activeContract->generatePaymentPeriods();
        }

        $service->load(['building.province', 'building.city', 'building.elevators', 'buildingContract']);
        $service->status_text = $service->status_text;
        $service->status_badge_class = $service->status_badge_class;
        $service->service_date_text = $service->service_date_text;

        return response()->json([
            'success' => true,
            'message' => 'سرویس با موفقیت ایجاد شد.',
            'data' => $service
        ], 201);
    }

    /**
     * Resend checklist SMS to building manager for a completed service
     */
    public function resendChecklistSms($id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $service = Service::with(['building', 'buildingContract'])
            ->whereHas('building', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            })
            ->findOrFail($id);

        // Refresh service to ensure slug is available
        $service->refresh();

        // Check if service is completed
        if ($service->status !== Service::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'فقط سرویس‌های انجام شده را می‌توان ارسال مجدد کرد.'
            ], 400);
        }

        // Check if building has manager phone and organization
        if (!$service->building || !$service->building->manager_phone) {
            return response()->json([
                'success' => false,
                'message' => 'شماره تماس مدیر ساختمان ثبت نشده است.'
            ], 400);
        }

        $service->load('building.organization');
        if (!$service->building->organization) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات سازمان یافت نشد.'
            ], 400);
        }

        $organization = $service->building->organization;
        
        // Format date_value as "آذر 1404" (month name + year) using service_month and service_year
        $monthNames = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
            5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
            9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
        ];
        $monthName = $monthNames[$service->service_month] ?? $service->service_month;
        $dateValue = $monthName . ' ' . $service->service_year;
        
        // Format URL value as "d/{service_slug}"
        $urlValue = 'd/' . $service->slug;
        
        // Get pattern code (same as submitChecklist)
        $patternCode = SmsPattern::getPatternCode('building_manager_checklist_submitted');
        
        if (!$patternCode) {
            return response()->json([
                'success' => false,
                'message' => 'الگوی پیامک یافت نشد.'
            ], 400);
        }

        $fillData = [
            'building_name' => $service->building->name,
            'date_value' => $dateValue,
            'organization_name' => $organization->name,
            'url_value' => $urlValue,
        ];
        
        $smsResult = $this->smsService->sendPatternSms(
            $organization,
            $patternCode,
            $fillData,
            $service->building->manager_phone,
            true // Use queue
        );

        if (!$smsResult['success']) {
            Log::error('Resend building manager checklist submitted SMS failed', [
                'service_id' => $service->id,
                'building_id' => $service->building->id,
                'phone_number' => $service->building->manager_phone,
                'error' => $smsResult['error'] ?? 'Unknown error',
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $smsResult['message'] ?? 'خطا در ارسال پیامک',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'پیامک با موفقیت ارسال شد.',
        ]);
    }

    /**
     * Generate temporary download URL for PDF (expires in 2 minutes)
     */
    public function generatePdfDownloadUrl($id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $service = Service::with(['building', 'buildingContract'])
            ->whereHas('building', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            })
            ->findOrFail($id);

        // Only allow completed services
        if ($service->status !== Service::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'فقط سرویس‌های انجام شده قابل دانلود هستند.'
            ], 400);
        }

        // Check if service has checklist
        $service->load('checklist.elevatorChecklists');
        if (!$service->checklist || $service->checklist->elevatorChecklists->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'چک لیست برای این سرویس موجود نیست.'
            ], 400);
        }

        // Generate unique token (60 chars so that 'org_' prefix fits in 64 char column)
        $token = Str::random(60);

        // Invalidate any existing unexpired tokens for this service (organization downloads)
        PdfVerificationCode::where('service_id', $service->id)
            ->where('download_token', 'like', 'org_%')
            ->where('expires_at', '>', now())
            ->delete();

        // Create verification code record (marked as verified for organization)
        $verificationCode = PdfVerificationCode::create([
            'service_id' => $service->id,
            'code' => 'ORG', // Special code for organization downloads
            'ip_address' => request()->ip(),
            'download_token' => 'org_' . $token,
            'used' => false,
            'verified' => true, // Pre-verified for organizations
            'expires_at' => now()->addMinutes(2), // 2 minutes expiration
            'verified_at' => now(),
        ]);

        // Generate download URL
        $downloadUrl = route('organization.services.pdf.download', [
            'service' => $service->id,
            'token' => $token
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'download_url' => $downloadUrl,
                'expires_at' => $verificationCode->expires_at->toIso8601String(),
                'expires_in_seconds' => 120
            ]
        ]);
    }

    /**
     * Download PDF for organization (no verification needed, just token check)
     */
    public function downloadPdf($service, Request $request)
    {
        // Handle route model binding - $service might be Service model or ID
        if (!$service instanceof Service) {
            $service = Service::with(['building', 'buildingContract'])->findOrFail($service);
        } else {
            $service->load(['building', 'buildingContract']);
        }

        // Only allow completed services
        if ($service->status !== Service::STATUS_COMPLETED) {
            abort(404, 'فقط سرویس‌های انجام شده قابل چاپ هستند.');
        }

        // Check for download token
        $token = $request->query('token');
        if (!$token) {
            abort(403, 'دسترسی غیرمجاز.');
        }

        // Verify token in database (organization tokens start with 'org_')
        $verificationCode = PdfVerificationCode::where('service_id', $service->id)
            ->where('download_token', 'org_' . $token)
            ->where('verified', true)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verificationCode) {
            abort(403, 'لینک دانلود منقضی شده است. لطفا دوباره درخواست کنید.');
        }

        // Load all necessary relationships
        $service->load([
            'building.organization',
            'building.province',
            'building.city',
            'technician',
            'checklist' => function($query) {
                $query->with([
                    'signatures',
                    'managerSignature',
                    'technicianSignature',
                    'elevatorChecklists.elevator',
                    'elevatorChecklists.descriptions'
                ]);
            }
        ]);

        if (!$service->checklist || $service->checklist->elevatorChecklists->count() === 0) {
            abort(404, 'چک لیست برای این سرویس موجود نیست.');
        }

        // Get unit checklists ordered by order
        $unitChecklists = UnitChecklist::orderBy('order')->get();

        // Month names in Persian
        $monthNames = [
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

        // Format completion date
        $completedDate = null;
        if ($service->completed_at) {
            try {
                if ($service->completed_at instanceof \Carbon\Carbon) {
                    $jalaliDate = Jalalian::fromCarbon($service->completed_at);
                } else {
                    $jalaliDate = Jalalian::fromDateTime($service->completed_at);
                }
                $completedDate = $jalaliDate->format('Y/m/d');
            } catch (\Exception $e) {
                $completedDate = $service->completed_at instanceof \Carbon\Carbon 
                    ? $service->completed_at->format('Y/m/d')
                    : date('Y/m/d', strtotime($service->completed_at));
            }
        }

        // Get signatures
        $checklist = $service->checklist;
        $allSignatures = $checklist->signatures;
        $technicianSig = $allSignatures->where('type', 'technician')->first();
        $managerSig = $allSignatures->where('type', 'manager')->first();
        
        if (!$technicianSig) {
            $technicianSig = $checklist->technicianSignature;
        }
        if (!$managerSig) {
            $managerSig = $checklist->managerSignature;
        }

        // Generate PDF using niklasravnsborg/laravel-pdf
        $pdf = Pdf::loadView('public.services.pdf', [
            'service' => $service,
            'building' => $service->building,
            'unitChecklists' => $unitChecklists,
            'monthNames' => $monthNames,
            'completedDate' => $completedDate,
            'technicianSig' => $technicianSig,
            'managerSig' => $managerSig,
        ]);

        $serviceMonthName = $monthNames[$service->service_month] ?? null;
        $serviceYear = $service->service_year ?? null;

        // Example: "آذر 1404 ساختمان برج میلاد.pdf"
        $filenameParts = array_filter([
            $serviceMonthName,
            $serviceYear,
            'ساختمان',
            $service->building->name,
        ], fn ($part) => !is_null($part) && $part !== '');

        $filename = implode(' ', $filenameParts) . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Handle financial record creation when service is completed or cancelled
     * Uses payment periods for dynamic record creation
     */
    private function handleServiceFinancialRecord(Service $service)
    {
        // Reload service to ensure we have the latest data
        $service->refresh();
        
        // Reload relationships
        $service->load(['buildingContract', 'paymentPeriod']);
        
        if (!$service->buildingContract || !$service->paymentPeriod) {
            // If service doesn't have a payment period, try to regenerate periods
            if ($service->buildingContract) {
                $service->buildingContract->generatePaymentPeriods();
                $service->refresh();
                $service->load(['buildingContract', 'paymentPeriod']);
                
                if (!$service->paymentPeriod) {
                    return; // Still no payment period, skip
                }
            } else {
                return; // No contract, skip
            }
        }

        $contract = $service->buildingContract;
        $paymentPeriod = $service->paymentPeriod;
        
        // Refresh payment period to get latest data
        $paymentPeriod->refresh();
        
        // Check if service is expired - expired services should not create financial records
        if ($service->status === Service::STATUS_EXPIRED) {
            return;
        }

        // Get all non-expired services in this payment period (fresh query)
        $allServicesInPeriod = $paymentPeriod->services()
            ->where('status', '!=', Service::STATUS_EXPIRED)
            ->get();
        
        if ($allServicesInPeriod->isEmpty()) {
            return; // No non-expired services in period
        }

        // Check if all non-expired services in this payment period are completed or cancelled
        $allFinished = $allServicesInPeriod->every(function ($s) {
            return in_array($s->status, [Service::STATUS_COMPLETED, Service::STATUS_CANCELLED]);
        });

        if (!$allFinished) {
            return; // Wait until all non-expired services in the period are finished
        }

        if ($contract->payment_timing === 'after_service') {
            // For after_service: Create financial record for current period when all services are finished
            $this->handleAfterServicePeriod($service, $contract, $paymentPeriod, $allServicesInPeriod);
        } elseif ($contract->payment_timing === 'before_service') {
            // For before_service: When current period is finished, create financial record for NEXT period
            $this->handleBeforeServicePeriod($service, $contract, $paymentPeriod);
        }
    }

    /**
     * Get Persian month name
     */
    private function getMonthName($month)
    {
        $monthNames = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
            5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
            9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
        ];
        return $monthNames[$month] ?? $month;
    }

    /**
     * Create description from services (for periods)
     */
    private function createDescriptionFromServices($services)
    {
        $serviceDescriptions = $services->map(function ($s) {
            $monthName = $this->getMonthName($s->service_month);
            return "{$monthName} {$s->service_year}";
        })->unique()->sort()->values();

        if ($serviceDescriptions->isEmpty()) {
            return 'از بابت سرویس';
        }

        return 'از بابت سرویس ' . $serviceDescriptions->implode('، ');
    }

    /**
     * Create financial record for expired or cancelled service
     */
    private function createFinancialRecordForExpiredOrCancelledService(Service $service, $type = 'expired')
    {
        if (!$service->building_contract_id) {
            return; // Only for services with contracts
        }

        $monthName = $this->getMonthName($service->service_month);
        $description = "از بابت سرویس {$monthName}";
        
        $extraDescriptions = $type === 'expired' 
            ? 'از بابت مراجعه نکردن به سرویس و گذشت سررسید مراجعه'
            : "از بابت کنسل کردن سرویس {$monthName}";

        // Check if record already exists
        $existingRecord = BuildingFinancialRecord::where('building_id', $service->building_id)
            ->where('building_contract_id', $service->building_contract_id)
            ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
            ->where('description', $description)
            ->where('amount', 0)
            ->where('extra_descriptions', $extraDescriptions)
            ->first();

        if (!$existingRecord) {
            BuildingFinancialRecord::create([
                'building_id' => $service->building_id,
                'building_contract_id' => $service->building_contract_id,
                'type' => BuildingFinancialRecord::TYPE_DEBIT,
                'amount' => 0,
                'transaction_type' => BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT,
                'description' => $description,
                'extra_descriptions' => $extraDescriptions,
                'transaction_date' => now(),
            ]);
        }
    }

    /**
     * Handle after_service payment timing - create record for current period
     */
    private function handleAfterServicePeriod(Service $service, $contract, $paymentPeriod, $allServicesInPeriod)
    {
        // Separate services by status
        $completedServices = $allServicesInPeriod->filter(function ($s) {
            return $s->status === Service::STATUS_COMPLETED;
        });

        $cancelledServices = $allServicesInPeriod->filter(function ($s) {
            return $s->status === Service::STATUS_CANCELLED;
        });

        // Create records for cancelled services with zero amount
        foreach ($cancelledServices as $cancelledService) {
            $monthName = $this->getMonthName($cancelledService->service_month);
            $description = "از بابت سرویس {$monthName}";
            $extraDescriptions = "از بابت کنسل کردن سرویس {$monthName}";

            $existingRecord = BuildingFinancialRecord::where('building_id', $service->building_id)
                ->where('building_contract_id', $contract->id)
                ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
                ->where('description', $description)
                ->where('amount', 0)
                ->where('extra_descriptions', $extraDescriptions)
                ->first();

            if (!$existingRecord) {
                BuildingFinancialRecord::create([
                    'building_id' => $service->building_id,
                    'building_contract_id' => $contract->id,
                    'type' => BuildingFinancialRecord::TYPE_DEBIT,
                    'amount' => 0,
                    'transaction_type' => BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT,
                    'description' => $description,
                    'extra_descriptions' => $extraDescriptions,
                    'transaction_date' => now(),
                ]);
            }
        }

        // Calculate total amount for completed services only
        $totalAmount = $completedServices->sum('monthly_amount');

        if ($totalAmount <= 0) {
            return; // No amount to record
        }

        // Create description from all services (completed + cancelled) for the period
        $allServicesForDescription = $allServicesInPeriod->filter(function ($s) {
            return in_array($s->status, [Service::STATUS_COMPLETED, Service::STATUS_CANCELLED]);
        });
        
        $description = $this->createDescriptionFromServices($allServicesForDescription);

        // Check if financial record already exists for this period
        $existingRecord = BuildingFinancialRecord::where('building_id', $service->building_id)
            ->where('building_contract_id', $contract->id)
            ->where('transaction_type', BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT)
            ->where('description', $description)
            ->where('amount', '>', 0)
            ->first();

        if ($existingRecord) {
            // Update existing record
            $existingRecord->update([
                'amount' => $totalAmount,
            ]);
        } else {
            // Create new financial record (debit - building owes money)
            BuildingFinancialRecord::create([
                'building_id' => $service->building_id,
                'building_contract_id' => $contract->id,
                'type' => BuildingFinancialRecord::TYPE_DEBIT,
                'amount' => $totalAmount,
                'transaction_type' => BuildingFinancialRecord::TRANSACTION_SERVICE_PAYMENT,
                'description' => $description,
                'transaction_date' => now(),
            ]);
        }
    }

    /**
     * Handle before_service payment timing - sync all pending periods
     * When a period's services are completed, we need to ensure the next period has a record
     */
    private function handleBeforeServicePeriod(Service $service, $contract, $paymentPeriod)
    {
        // Reload contract with payment periods and services to ensure fresh data
        $contract->refresh();
        $contract->load(['paymentPeriods.services']);
        
        // Reload the payment period with services
        $paymentPeriod->refresh();
        $paymentPeriod->load('services');
        
        // For before_service, we sync all periods that should be pending
        // This ensures when a period finishes, the next period gets a financial record
        $contract->syncBeforeServiceFinancialRecords();
    }
}
