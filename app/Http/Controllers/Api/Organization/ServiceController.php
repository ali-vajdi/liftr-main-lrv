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
        Service::whereHas('building', function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            })
            ->whereIn('status', [Service::STATUS_PENDING, Service::STATUS_ASSIGNED])
            ->where(function ($query) use ($currentYear, $currentMonth) {
                // Services from previous years
                $query->where('service_year', '<', $currentYear)
                    // OR services from current year but previous months
                    ->orWhere(function ($q) use ($currentYear, $currentMonth) {
                        $q->where('service_year', $currentYear)
                          ->where('service_month', '<', $currentMonth);
                    });
            })
            ->update(['status' => Service::STATUS_EXPIRED]);

        foreach ($buildings as $building) {
            try {

                // Get all existing services for this building (including expired for checking, but excluding for latest calculation)
                // We need to check ALL services (including expired) to see if a month already has a service
                // But we only use non-expired services to determine the latest service
                $allServices = Service::where('building_id', $building->id)
                    ->orderBy('service_year', 'asc')
                    ->orderBy('service_month', 'asc')
                    ->get();

                // Get non-expired services to find the latest
                $existingServices = $allServices->where('status', '!=', Service::STATUS_EXPIRED)->values();

                // Check service_end_date - if contract has ended, don't generate beyond that month
                $endYear = null;
                $endMonth = null;
                if ($building->service_end_date) {
                    $endDateJalali = Jalalian::forge($building->service_end_date);
                    $endYear = $endDateJalali->getYear();
                    $endMonth = $endDateJalali->getMonth();
                    // Allow generation for the end month itself
                }

                // Determine the range to generate services
                // Start from the earliest missing month up to current month
                if ($existingServices->isEmpty()) {
                    // No services exist, generate from current month only
                    $startYear = $currentYear;
                    $startMonth = $currentMonth;
                } else {
                    // Find the latest service
                    $latestService = $existingServices->last();
                    $latestYear = $latestService->service_year;
                    $latestMonth = $latestService->service_month;

                    // Start from the month after the latest service, or current month if latest is in the future
                    if ($latestYear > $currentYear || ($latestYear == $currentYear && $latestMonth >= $currentMonth)) {
                        // Latest service is current or future month, only generate current month if missing
                        $startYear = $currentYear;
                        $startMonth = $currentMonth;
                    } else {
                        // Start from the month after the latest service
                        $startMonth = $latestMonth + 1;
                        $startYear = $latestYear;
                        if ($startMonth > 12) {
                            $startMonth = 1;
                            $startYear++;
                        }
                    }
                }

                // Generate services from start to current month (or end date if earlier)
                $year = $startYear;
                $month = $startMonth;

                while (true) {
                    // Stop if we've passed the current month
                    if ($year > $currentYear || ($year == $currentYear && $month > $currentMonth)) {
                        break;
                    }

                    // Stop if we've passed the service_end_date month (but allow the end month itself)
                    if ($endYear !== null && $endMonth !== null) {
                        if ($year > $endYear || ($year == $endYear && $month > $endMonth)) {
                            break;
                        }
                    }

                    // Check if service already exists for this month/year (regardless of status)
                    $existingService = Service::where('building_id', $building->id)
                        ->where('service_month', $month)
                        ->where('service_year', $year)
                        ->first();

                    if (!$existingService) {
                        // Create new service - one service per month per building
                        Service::create([
                            'building_id' => $building->id,
                            'service_month' => $month,
                            'service_year' => $year,
                            'status' => Service::STATUS_PENDING,
                        ]);
                    } else if ($existingService->status === Service::STATUS_EXPIRED) {
                        // If service exists but is expired, and it's current month, reactivate it
                        if ($year == $currentYear && $month == $currentMonth) {
                            $existingService->update(['status' => Service::STATUS_PENDING]);
                        }
                    }

                    // Move to next month
                    $month++;
                    if ($month > 12) {
                        $month = 1;
                        $year++;
                    }
                }

                // Ensure current month always has a service (even if it was expired)
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
                        Service::create([
                            'building_id' => $building->id,
                            'service_month' => $currentMonth,
                            'service_year' => $currentYear,
                            'status' => Service::STATUS_PENDING,
                        ]);
                    }
                } else if ($currentMonthService->status === Service::STATUS_EXPIRED) {
                    // If current month service exists but is expired, reactivate it as pending
                    $currentMonthService->update(['status' => Service::STATUS_PENDING]);
                }
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
            ->pending()
            // Only show current month pending services
            ->where('service_year', $currentYear)
            ->where('service_month', $currentMonth);

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
            
            // Add assigned information
            if ($service->assigned_at) {
                $service->assigned_at_jalali = Jalalian::forge($service->assigned_at)->format('Y/m/d H:i:s');
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
}
