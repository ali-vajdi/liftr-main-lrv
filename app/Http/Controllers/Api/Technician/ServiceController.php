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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class ServiceController extends Controller
{
    /**
     * Get assigned buildings/services for the authenticated technician
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
            ->orderBy('assigned_at', 'desc')
            ->orderBy('service_year', 'desc')
            ->orderBy('service_month', 'desc')
            ->get();

        // Format response - only return required fields
        $items = $services->map(function ($service) {
            $building = $service->building;
            $elevatorsCount = $building && $building->elevators ? $building->elevators->count() : 0;
            
            return [
                'id' => $service->id,
                'assigned_at_jalali' => $service->assigned_at ? Jalalian::forge($service->assigned_at)->format('Y/m/d H:i:s') : null,
                'building_name' => $building ? $building->name : null,
                'building_address' => $building ? $building->address : null,
                'elevators_count' => $elevatorsCount,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items->all()
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
            ]);

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
