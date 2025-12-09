<?php

namespace App\Http\Controllers\Api\Technician;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\UnitChecklist;
use App\Models\DescriptionChecklist;
use App\Models\ServiceChecklist;
use App\Models\ServiceElevatorChecklist;
use App\Models\ServiceChecklistDescription;
use App\Models\ServiceSignature;
use App\Models\ServiceChecklistHistory;
use App\Rules\ChecklistIdRule;
use App\Services\SmsService;
use App\Services\SmsPattern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;

class ServiceController extends Controller
{
    /**
     * Get assigned buildings/services for the authenticated technician
     * Grouped by visit_date and visit_time_range
     */
    public function assignedBuildings(Request $request)
    {
        $technician = auth('technician_api')->user();
        if (!$technician) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Only load building and elevators count - get all assigned services
        $services = Service::with([
            'building' => function ($query) {
                $query->select('id', 'name', 'address');
            },
            'building.elevators' => function ($query) {
                $query->select('id', 'building_id');
            }
        ])
            ->where('technician_id', $technician->id)
            ->assigned() // Only assigned services
            ->whereNotNull('visit_date')
            ->whereNotNull('visit_time_range')
            ->orderBy('visit_date', 'asc')
            ->orderByRaw("CASE 
                WHEN visit_time_range = '06:00 - 08:00' THEN 1
                WHEN visit_time_range = '08:00 - 10:00' THEN 2
                WHEN visit_time_range = '10:00 - 12:00' THEN 3
                WHEN visit_time_range = '12:00 - 14:00' THEN 4
                WHEN visit_time_range = '14:00 - 16:00' THEN 5
                WHEN visit_time_range = '16:00 - 18:00' THEN 6
                WHEN visit_time_range = '18:00 - 20:00' THEN 7
                WHEN visit_time_range = '20:00 - 22:00' THEN 8
                WHEN visit_time_range = '22:00 - 24:00' THEN 9
                ELSE 10
            END")
            ->get();

        // Get current Jalali date for comparison
        $now = Jalalian::now();
        $todayCarbon = $now->toCarbon();
        $todayDate = $todayCarbon->format('Y-m-d');
        $tomorrowDate = $todayCarbon->copy()->addDay()->format('Y-m-d');
        $yesterdayDate = $todayCarbon->copy()->subDay()->format('Y-m-d');
        $dayBeforeYesterdayDate = $todayCarbon->copy()->subDays(2)->format('Y-m-d'); // پریروز
        $dayAfterTomorrowDate = $todayCarbon->copy()->addDays(2)->format('Y-m-d');

        // Group services by date and time range
        $grouped = [];
        
        foreach ($services as $service) {
            $building = $service->building;
            $elevatorsCount = $building && $building->elevators ? $building->elevators->count() : 0;
            
            // Format service data
            $serviceData = [
                'id' => $service->id,
                'assigned_at_jalali' => $service->assigned_at ? Jalalian::forge($service->assigned_at)->format('Y/m/d H:i:s') : null,
                'building_name' => $building ? $building->name : null,
                'building_address' => $building ? $building->address : null,
                'elevators_count' => $elevatorsCount,
                'visit_date' => $service->visit_date ? Jalalian::forge($service->visit_date)->format('Y/m/d') : null,
                'visit_time_range' => $service->visit_time_range,
            ];

            // Get visit date for grouping
            $visitDate = $service->visit_date ? $service->visit_date->format('Y-m-d') : null;
            
            if (!$visitDate || !$service->visit_time_range) {
                continue; // Skip services without visit_date or visit_time_range
            }

            // Format date label
            // Label: پریروز (day before yesterday), دیروز (yesterday), امروز (today), فردا (tomorrow), پس فردا (day after tomorrow)
            // Special labels include the date in parentheses, all other dates show actual date only
            $visitDateJalali = Jalalian::forge($service->visit_date)->format('Y/m/d');
            $dateLabel = null;
            if ($visitDate === $todayDate) {
                $dateLabel = 'امروز(' . $visitDateJalali . ')';
            } elseif ($visitDate === $tomorrowDate) {
                $dateLabel = 'فردا(' . $visitDateJalali . ')';
            } elseif ($visitDate === $yesterdayDate) {
                $dateLabel = 'دیروز(' . $visitDateJalali . ')';
            } elseif ($visitDate === $dayBeforeYesterdayDate) {
                $dateLabel = 'پریروز(' . $visitDateJalali . ')';
            } elseif ($visitDate === $dayAfterTomorrowDate) {
                $dateLabel = 'پس فردا(' . $visitDateJalali . ')';
            } else {
                // Use Jalali date format for all other dates
                $dateLabel = $visitDateJalali;
            }

            // Initialize date group if not exists
            if (!isset($grouped[$dateLabel])) {
                $grouped[$dateLabel] = [
                    'is_passed' => false,
                    'time_ranges' => []
                ];
                // Check if this date is in the past (strictly less than today, not equal)
                if ($service->visit_date) {
                    $visitDateCarbon = $service->visit_date->startOfDay();
                    $todayCarbon = Jalalian::now()->toCarbon()->startOfDay();
                    // Only mark as passed if the date is strictly before today (not today or future)
                    $grouped[$dateLabel]['is_passed'] = $visitDateCarbon->lt($todayCarbon);
                }
            }

            // Initialize time range group if not exists
            if (!isset($grouped[$dateLabel]['time_ranges'][$service->visit_time_range])) {
                $grouped[$dateLabel]['time_ranges'][$service->visit_time_range] = [];
            }

            // Add service to the appropriate group
            $grouped[$dateLabel]['time_ranges'][$service->visit_time_range][] = $serviceData;
        }

        // Sort time ranges within each date group chronologically
        $timeRangeOrder = [
            '06:00 - 08:00' => 1,
            '08:00 - 10:00' => 2,
            '10:00 - 12:00' => 3,
            '12:00 - 14:00' => 4,
            '14:00 - 16:00' => 5,
            '16:00 - 18:00' => 6,
            '18:00 - 20:00' => 7,
            '20:00 - 22:00' => 8,
            '22:00 - 24:00' => 9,
        ];
        
        foreach ($grouped as $dateLabel => &$dateGroup) {
            uksort($dateGroup['time_ranges'], function($a, $b) use ($timeRangeOrder) {
                $orderA = $timeRangeOrder[$a] ?? 999;
                $orderB = $timeRangeOrder[$b] ?? 999;
                return $orderA <=> $orderB;
            });
        }
        unset($dateGroup);
        
        // Sort date groups: past dates (oldest first), then پریروز, دیروز, امروز, فردا, پس فردا, then future dates (newest first)
        $sortedGrouped = [];
        // Note: Special dates now include the date in the label, so we need to check if label starts with special text
        $specialDatePrefixes = ['پریروز', 'دیروز', 'امروز', 'فردا', 'پس فردا'];
        
        // Separate special dates, past dates, and future dates
        $specialDates = [];
        $pastDates = [];
        $futureDates = [];
        
        $todayCarbon = Jalalian::now()->toCarbon();
        
        foreach ($grouped as $dateLabel => $dateGroup) {
            // Check if label starts with any special date prefix
            $isSpecialDate = false;
            $specialDateType = null;
            foreach ($specialDatePrefixes as $prefix) {
                if (strpos($dateLabel, $prefix) === 0) {
                    $isSpecialDate = true;
                    $specialDateType = $prefix;
                    break;
                }
            }
            
            if ($isSpecialDate) {
                $specialDates[$dateLabel] = ['type' => $specialDateType, 'data' => $dateGroup];
            } else {
                // Parse the date to determine if it's past or future
                try {
                    $dateParts = explode('/', $dateLabel);
                    if (count($dateParts) === 3) {
                        $jalaliDate = Jalalian::fromFormat('Y/m/d', $dateLabel);
                        $dateCarbon = $jalaliDate->toCarbon();
                        
                        if ($dateCarbon->lt($todayCarbon)) {
                            // Past date
                            $pastDates[$dateLabel] = ['carbon' => $dateCarbon, 'data' => $dateGroup];
                        } else {
                            // Future date
                            $futureDates[$dateLabel] = ['carbon' => $dateCarbon, 'data' => $dateGroup];
                        }
                    } else {
                        // Unknown format, add to past dates
                        $pastDates[$dateLabel] = ['carbon' => null, 'data' => $dateGroup];
                    }
                } catch (\Exception $e) {
                    // If parsing fails, add to past dates
                    $pastDates[$dateLabel] = ['carbon' => null, 'data' => $dateGroup];
                }
            }
        }
        
        // Sort past dates (oldest first) - these come FIRST
        uasort($pastDates, function($a, $b) {
            if ($a['carbon'] === null && $b['carbon'] === null) {
                return 0;
            }
            if ($a['carbon'] === null) {
                return 1;
            }
            if ($b['carbon'] === null) {
                return -1;
            }
            return $a['carbon']->lt($b['carbon']) ? -1 : 1;
        });
        foreach ($pastDates as $dateLabel => $dateData) {
            $sortedGrouped[$dateLabel] = $dateData['data'];
        }
        
        // Add special dates in order - these come AFTER past dates
        // Sort special dates by their type (پریروز, دیروز, امروز, فردا, پس فردا)
        $specialDateOrder = ['پریروز', 'دیروز', 'امروز', 'فردا', 'پس فردا'];
        uasort($specialDates, function($a, $b) use ($specialDateOrder) {
            $indexA = array_search($a['type'], $specialDateOrder);
            $indexB = array_search($b['type'], $specialDateOrder);
            return $indexA <=> $indexB;
        });
        foreach ($specialDates as $dateLabel => $dateInfo) {
            $sortedGrouped[$dateLabel] = $dateInfo['data'];
        }
        
        // Sort future dates (newest first) - these come LAST
        uasort($futureDates, function($a, $b) {
            if ($a['carbon'] === null && $b['carbon'] === null) {
                return 0;
            }
            if ($a['carbon'] === null) {
                return 1;
            }
            if ($b['carbon'] === null) {
                return -1;
            }
            return $b['carbon']->lt($a['carbon']) ? -1 : 1;
        });
        foreach ($futureDates as $dateLabel => $dateData) {
            $sortedGrouped[$dateLabel] = $dateData['data'];
        }
        
        // Restructure the response to have is_passed at the top level and time_ranges as the data
        $finalResponse = [];
        foreach ($sortedGrouped as $dateLabel => $dateGroup) {
            $finalResponse[$dateLabel] = [
                'is_passed' => $dateGroup['is_passed'],
                ...$dateGroup['time_ranges']
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $finalResponse
        ]);
    }

    /**
     * Get a specific assigned service/building details
     */
    public function show($id)
    {
        $technician = auth('technician_api')->user();
        if (!$technician) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $service = Service::with([
            'building.province', 
            'building.city', 
            'building.elevators',
        ])
            ->where('technician_id', $technician->id)
            ->findOrFail($id);

        $service->status_text = $service->status_text;
        $service->status_badge_class = $service->status_badge_class;
        $service->service_date_text = $service->service_date_text;
        if ($service->assigned_at) {
            $service->assigned_at_jalali = Jalalian::forge($service->assigned_at)->format('Y/m/d H:i:s');
        }
        if ($service->completed_at) {
            $service->completed_at_jalali = Jalalian::forge($service->completed_at)->format('Y/m/d H:i:s');
        }
        
        // Note: organization_note and user_note are automatically included in the response
        // as they are in the Service model's fillable array

        // Get last service (previous month's service for the same building)
        $lastService = null;

        $lastService = Service::with([
            'building.province', 
            'building.city', 
            'building.elevators',
            'checklist.elevatorChecklists.elevator',
            'checklist.elevatorChecklists.descriptions.checklist',
            'checklist.managerSignature',
            'checklist.technicianSignature',
        ])
            ->where('status', Service::STATUS_COMPLETED)
            ->where('building_id', $service->building_id)
            ->orderByDesc('id')
            ->first();

        if ($lastService) {
            $lastService->status_text = $lastService->status_text;
            $lastService->status_badge_class = $lastService->status_badge_class;
            $lastService->service_date_text = $lastService->service_date_text;
            if ($lastService->assigned_at) {
                $lastService->assigned_at_jalali = Jalalian::forge($lastService->assigned_at)->format('Y/m/d H:i:s');
            }
            if ($lastService->completed_at) {
                $lastService->completed_at_jalali = Jalalian::forge($lastService->completed_at)->format('Y/m/d H:i:s');
            }
        }

        // Get unit checklists ordered by order field
        $unitChecklists = UnitChecklist::orderBy('order', 'asc')
            ->select('id', 'title', 'order')
            ->get();

        // Get description checklists ordered by order field
        $descriptionChecklists = DescriptionChecklist::orderBy('order', 'asc')
            ->select('id', 'title', 'order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $service,
            'last_service' => $lastService,
            'checklists' => $unitChecklists,
            'description_checklists' => $descriptionChecklists
        ]);
    }

    /**
     * Submit checklist for a service
     */
    public function submitChecklist(Request $request, $serviceId)
    {
        $technician = auth('technician_api')->user();
        if (!$technician) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make(array_merge($request->all(), ['service_id' => $serviceId]), [
            'service_id' => 'required|exists:services,id',
            'elevators' => 'required|array|min:1',
            'elevators.*.elevator_id' => 'required|exists:elevators,id',
            'elevators.*.verified' => 'required|boolean',
            'elevators.*.descriptions' => 'nullable|array',
            'elevators.*.descriptions.*.checklist_id' => ['required', 'integer', new ChecklistIdRule()],
            'elevators.*.descriptions.*.title' => 'required|string|max:255',
            'elevators.*.descriptions.*.description' => 'nullable|string',
            'manager_signature.name' => 'required|string|max:255',
            'manager_signature.signature' => 'required|string',
            'technician_signature.name' => 'required|string|max:255',
            'technician_signature.signature' => 'required|string',
            'technician_note' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Verify service belongs to technician
        $service = Service::with('building')
            ->where('technician_id', $technician->id)
            ->where('id', $data['service_id'])
            ->firstOrFail();

        // Verify elevators belong to the service's building
        $elevatorIds = collect($data['elevators'])->pluck('elevator_id')->toArray();
        $validElevators = DB::table('elevators')
            ->where('building_id', $service->building_id)
            ->whereIn('id', $elevatorIds)
            ->pluck('id')
            ->toArray();

        if (count($elevatorIds) !== count($validElevators)) {
            return response()->json([
                'success' => false,
                'message' => 'Some elevators do not belong to this building'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Check if checklist already exists (for updates)
            $serviceChecklist = ServiceChecklist::where('service_id', $service->id)->first();
            $isUpdate = $serviceChecklist !== null;
            $oldData = null;

            if ($isUpdate) {
                // Load old data for history
                $oldData = [
                    'elevators' => $serviceChecklist->elevatorChecklists->map(function ($elevatorChecklist) {
                        return [
                            'elevator_id' => $elevatorChecklist->elevator_id,
                            'verified' => $elevatorChecklist->verified,
                            'descriptions' => $elevatorChecklist->descriptions->map(function ($desc) {
                                return [
                                    'checklist_id' => $desc->checklist_id,
                                    'title' => $desc->title,
                                    'description' => $desc->description,
                                ];
                            })->toArray(),
                        ];
                    })->toArray(),
                    'signatures' => [
                        'manager' => $serviceChecklist->managerSignature ? [
                            'name' => $serviceChecklist->managerSignature->name,
                        ] : null,
                        'technician' => $serviceChecklist->technicianSignature ? [
                            'name' => $serviceChecklist->technicianSignature->name,
                        ] : null,
                    ],
                ];

                // Delete old data
                $serviceChecklist->elevatorChecklists()->delete();
                $serviceChecklist->signatures()->delete();
            } else {
                // Create new service checklist
                $serviceChecklist = ServiceChecklist::create([
                    'service_id' => $service->id,
                    'technician_id' => $technician->id,
                    'submitted_at' => now(),
                ]);
            }

            // Save elevator checklists
            foreach ($data['elevators'] as $elevatorData) {
                $elevatorChecklist = ServiceElevatorChecklist::create([
                    'service_checklist_id' => $serviceChecklist->id,
                    'elevator_id' => $elevatorData['elevator_id'],
                    'verified' => $elevatorData['verified'],
                ]);

                // Save descriptions for this elevator (if provided)
                if (!empty($elevatorData['descriptions']) && is_array($elevatorData['descriptions'])) {
                    foreach ($elevatorData['descriptions'] as $descriptionData) {
                        // Convert checklist_id 0 (or "0") to NULL for custom checklists
                        $checklistId = ((int) $descriptionData['checklist_id']) == 0 ? null : (int) $descriptionData['checklist_id'];
                        
                        ServiceChecklistDescription::create([
                            'service_elevator_checklist_id' => $elevatorChecklist->id,
                            'checklist_id' => $checklistId,
                            'title' => $descriptionData['title'],
                            'description' => $descriptionData['description'] ?? null,
                        ]);
                    }
                }
            }

            // Save signatures
            ServiceSignature::create([
                'service_checklist_id' => $serviceChecklist->id,
                'type' => 'manager',
                'name' => $data['manager_signature']['name'],
                'signature' => $data['manager_signature']['signature'],
            ]);

            ServiceSignature::create([
                'service_checklist_id' => $serviceChecklist->id,
                'type' => 'technician',
                'name' => $data['technician_signature']['name'],
                'signature' => $data['technician_signature']['signature'],
            ]);

            // Create history entry
            $newData = [
                'elevators' => $data['elevators'],
                'signatures' => [
                    'manager' => ['name' => $data['manager_signature']['name']],
                    'technician' => ['name' => $data['technician_signature']['name']],
                ],
            ];

            ServiceChecklistHistory::create([
                'service_checklist_id' => $serviceChecklist->id,
                'technician_id' => $technician->id,
                'action' => $isUpdate ? 'updated' : 'created',
                'changes' => $isUpdate ? [
                    'old' => $oldData,
                    'new' => $newData,
                ] : null,
                'notes' => $isUpdate ? 'Checklist updated' : 'Checklist submitted',
                'created_at' => now(),
            ]);

            // Update service status to completed
            $service->update([
                'status' => Service::STATUS_COMPLETED,
                'completed_at' => now(),
                'technician_note' => $request->technician_note ?? null,
            ]);

            // Send SMS to building manager
            $service->load('building.organization');
            if ($service->building && $service->building->manager_phone && $service->building->organization) {
                $organization = $service->building->organization;
                
                // Format date_value as Jalali date (e.g., "آبان 1404")
                $dateValue = '';
                if ($service->visit_date) {
                    try {
                        $jalaliDate = Jalalian::forge($service->visit_date);
                        $monthNames = [
                            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
                            5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
                            9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
                        ];
                        $monthName = $monthNames[$jalaliDate->getMonth()] ?? $jalaliDate->getMonth();
                        $year = $jalaliDate->getYear();
                        $dateValue = $monthName . ' ' . $year;
                    } catch (\Exception $e) {
                        // Fallback to service date if visit_date parsing fails
                        $monthNames = [
                            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
                            5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
                            9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
                        ];
                        $monthName = $monthNames[$service->service_month] ?? $service->service_month;
                        $dateValue = $monthName . ' ' . $service->service_year;
                        Log::warning('Failed to format visit_date for SMS, using service_date', [
                            'service_id' => $service->id,
                            'visit_date' => $service->visit_date,
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    // Use service date if visit_date is not set
                    $monthNames = [
                        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
                        5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
                        9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
                    ];
                    $monthName = $monthNames[$service->service_month] ?? $service->service_month;
                    $dateValue = $monthName . ' ' . $service->service_year;
                }
                
                // Format URL value as "d/{service_slug}"
                $urlValue = 'd/' . $service->slug;
                
                // Get pattern code
                $patternCode = SmsPattern::getPatternCode('building_manager_checklist_submitted');
                
                if ($patternCode) {
                    $smsService = new SmsService();
                    $fillData = [
                        'building_name' => $service->building->name,
                        'date_value' => $dateValue,
                        'organization_name' => $organization->name,
                        'url_value' => $urlValue,
                    ];
                    
                    $smsResult = $smsService->sendPatternSms(
                        $organization,
                        $patternCode,
                        $fillData,
                        $service->building->manager_phone,
                        true // Use queue
                    );
                    
                    if (!$smsResult['success']) {
                        Log::error('Building manager checklist submitted SMS failed', [
                            'service_id' => $service->id,
                            'building_id' => $service->building->id,
                            'phone_number' => $service->building->manager_phone,
                            'error' => $smsResult['error'] ?? 'Unknown error',
                        ]);
                    }
                } else {
                    Log::warning('SMS pattern code not found for building_manager_checklist_submitted', [
                        'service_id' => $service->id,
                    ]);
                }
            }

            // Generate next month's service if it doesn't exist
            $nextMonth = $service->service_month + 1;
            $nextYear = $service->service_year;
            if ($nextMonth > 12) {
                $nextMonth = 1;
                $nextYear++;
            }

            // Check if next month's service exists
            $nextService = Service::where('building_id', $service->building_id)
                ->where('service_month', $nextMonth)
                ->where('service_year', $nextYear)
                ->first();

            // Check service_end_date - only generate if contract hasn't ended
            $shouldGenerate = true;
            if ($service->building->service_end_date) {
                $endDateJalali = \Morilog\Jalali\Jalalian::forge($service->building->service_end_date);
                $endYear = $endDateJalali->getYear();
                $endMonth = $endDateJalali->getMonth();
                
                // Don't generate if next month is after end date (but allow the end month itself)
                if ($nextYear > $endYear || ($nextYear == $endYear && $nextMonth > $endMonth)) {
                    $shouldGenerate = false;
                }
            }

            if (!$nextService && $shouldGenerate) {
                Service::create([
                    'building_id' => $service->building_id,
                    'service_month' => $nextMonth,
                    'service_year' => $nextYear,
                    'status' => Service::STATUS_PENDING,
                ]);
            }

            DB::commit();

            // Load relationships for response
            $serviceChecklist->load([
                'elevatorChecklists.elevator',
                'elevatorChecklists.descriptions.checklist',
                'managerSignature',
                'technicianSignature',
            ]);

            return response()->json([
                'success' => true,
                'message' => $isUpdate ? 'Checklist updated successfully' : 'Checklist submitted successfully',
                'data' => $serviceChecklist
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error submitting checklist: ' . $e->getMessage()
            ], 500);
        }
    }
}
