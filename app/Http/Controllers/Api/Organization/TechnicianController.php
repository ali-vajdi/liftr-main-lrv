<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use App\Models\OrganizationUser;
use App\Models\Service;
use App\Models\Organization;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class TechnicianController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }
    public function index(Request $request)
    {
        // Get organization ID from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $organizationId = $user->organization_id;
        
        $query = Technician::where('organization_id', $organizationId)
            ->with(['organization', 'organizationUser']);

        // Filtering and sorting
        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('national_id', 'like', '%' . $search . '%')
                    ->orWhere('phone_number', 'like', '%' . $search . '%');
            });
        }

        $sortField = $request->get('sort_field', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->get('per_page', 10);
        $technicians = $query->paginate($perPage);

        // Add calculated attributes to each item
        $items = $technicians->items();
        foreach ($items as $item) {
            $item->full_name = $item->full_name;
            $item->status_text = $item->status_text;
            $item->status_badge_class = $item->status_badge_class;
            $item->has_credentials = $item->has_credentials;
            $item->credentials_status_text = $item->credentials_status_text;
            $item->credentials_status_badge_class = $item->credentials_status_badge_class;
        }

        return response()->json([
            'data' => $items,
            'current_page' => $technicians->currentPage(),
            'last_page' => $technicians->lastPage(),
            'per_page' => $technicians->perPage(),
            'total' => $technicians->total(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'national_id' => 'required|string|unique:technicians,national_id',
            'phone_number' => 'required|string|max:20',
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:true,false',
        ], [
            'first_name.required' => 'نام الزامی است',
            'last_name.required' => 'نام خانوادگی الزامی است',
            'national_id.required' => 'کد ملی الزامی است',
            'national_id.unique' => 'کد ملی تکراری است',
            'phone_number.required' => 'شماره تماس الزامی است',
            'password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد',
            'status.required' => 'وضعیت الزامی است',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get organization ID from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->all();
        $data['organization_id'] = $user->organization_id;
        $data['organization_user_id'] = $user->id;
        $data['status'] = $data['status'] === 'true' || $data['status'] === true;

        // Store plain password before hashing (for SMS)
        $plainPassword = $data['password'] ?? null;

        $technician = Technician::create($data);

        // Get organization for SMS
        $organization = Organization::findOrFail($user->organization_id);
        $technicianName = $technician->full_name;

        // Send SMS based on whether password was provided
        if ($plainPassword) {
            // Send SMS with password
            $smsResult = $this->smsService->sendTechnicianWelcomeWithPasswordSms(
                $organization,
                $technician->phone_number,
                $technicianName,
                $plainPassword,
                true // Use queue
            );
        } else {
            // Send SMS without password
            $smsResult = $this->smsService->sendTechnicianWelcomeNoPasswordSms(
                $organization,
                $technician->phone_number,
                $technicianName,
                true // Use queue
            );
        }

        if (!$smsResult['success']) {
            Log::error('Technician welcome SMS failed', [
                'technician_id' => $technician->id,
                'phone_number' => $technician->phone_number,
                'error' => $smsResult['error'] ?? 'Unknown error',
            ]);
        }

        // Add calculated attributes
        $technician->full_name = $technician->full_name;
        $technician->status_text = $technician->status_text;
        $technician->status_badge_class = $technician->status_badge_class;
        $technician->has_credentials = $technician->has_credentials;
        $technician->credentials_status_text = $technician->credentials_status_text;
        $technician->credentials_status_badge_class = $technician->credentials_status_badge_class;

        return response()->json([
            'message' => 'تکنیسین با موفقیت ایجاد شد',
            'data' => $technician->load(['organization', 'organizationUser'])
        ], 201);
    }

    public function show($id)
    {
        // Get organization ID from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $organizationId = $user->organization_id;
        
        $technician = Technician::where('organization_id', $organizationId)
            ->where('id', $id)
            ->with(['organization', 'organizationUser'])
            ->first();

        if (!$technician) {
            return response()->json([
                'message' => 'تکنیسین مورد نظر یافت نشد'
            ], 404);
        }

        // Add calculated attributes
        $technician->full_name = $technician->full_name;
        $technician->status_text = $technician->status_text;
        $technician->status_badge_class = $technician->status_badge_class;
        $technician->has_credentials = $technician->has_credentials;
        $technician->credentials_status_text = $technician->credentials_status_text;
        $technician->credentials_status_badge_class = $technician->credentials_status_badge_class;

        return response()->json([
            'data' => $technician
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'national_id' => 'required|string|unique:technicians,national_id,' . $id,
            'phone_number' => 'required|string|max:20',
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:true,false',
        ], [
            'first_name.required' => 'نام الزامی است',
            'last_name.required' => 'نام خانوادگی الزامی است',
            'national_id.required' => 'کد ملی الزامی است',
            'national_id.unique' => 'کد ملی تکراری است',
            'phone_number.required' => 'شماره تماس الزامی است',
            'password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد',
            'status.required' => 'وضعیت الزامی است',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get organization ID from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $organizationId = $user->organization_id;
        
        $technician = Technician::where('organization_id', $organizationId)
            ->where('id', $id)
            ->first();

        if (!$technician) {
            return response()->json([
                'message' => 'تکنیسین مورد نظر یافت نشد'
            ], 404);
        }

        $data = $request->all();
        $data['status'] = $data['status'] === 'true' || $data['status'] === true;
        
        // Store plain password before hashing (for SMS)
        $plainPassword = $data['password'] ?? null;
        $passwordChanged = !empty($plainPassword);
        
        // Only update password if provided
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $technician->update($data);

        // Send password changed SMS if password was updated
        if ($passwordChanged) {
            $organization = Organization::findOrFail($organizationId);
            $technicianName = $technician->full_name;
            
            $smsResult = $this->smsService->sendTechnicianPasswordChangedSms(
                $organization,
                $technician->phone_number,
                $technicianName,
                $plainPassword,
                true // Use queue
            );

            if (!$smsResult['success']) {
                Log::error('Technician password changed SMS failed', [
                    'technician_id' => $technician->id,
                    'phone_number' => $technician->phone_number,
                    'error' => $smsResult['error'] ?? 'Unknown error',
                ]);
            }
        }

        // Add calculated attributes
        $technician->full_name = $technician->full_name;
        $technician->status_text = $technician->status_text;
        $technician->status_badge_class = $technician->status_badge_class;
        $technician->has_credentials = $technician->has_credentials;
        $technician->credentials_status_text = $technician->credentials_status_text;
        $technician->credentials_status_badge_class = $technician->credentials_status_badge_class;

        return response()->json([
            'message' => 'تکنیسین با موفقیت ویرایش شد',
            'data' => $technician->fresh(['organization', 'organizationUser'])
        ]);
    }

    public function destroy($id)
    {
        // Get organization ID from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $organizationId = $user->organization_id;
        
        $technician = Technician::where('organization_id', $organizationId)
            ->where('id', $id)
            ->first();

        if (!$technician) {
            return response()->json([
                'message' => 'تکنیسین مورد نظر یافت نشد'
            ], 404);
        }

        $technician->delete();

        return response()->json([
            'message' => 'تکنیسین با موفقیت حذف شد'
        ]);
    }

    public function setCredentials(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
        ], [
            'password.required' => 'رمز عبور الزامی است',
            'password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get organization ID from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $organizationId = $user->organization_id;
        
        $technician = Technician::where('organization_id', $organizationId)
            ->where('id', $id)
            ->first();

        if (!$technician) {
            return response()->json([
                'message' => 'تکنیسین مورد نظر یافت نشد'
            ], 404);
        }

        $plainPassword = $request->password;

        $technician->update([
            'password' => $plainPassword,
        ]);

        // Send password changed SMS
        $organization = Organization::findOrFail($organizationId);
        $technicianName = $technician->full_name;
        
        $smsResult = $this->smsService->sendTechnicianPasswordChangedSms(
            $organization,
            $technician->phone_number,
            $technicianName,
            $plainPassword,
            true // Use queue
        );

        if (!$smsResult['success']) {
            Log::error('Technician password changed SMS failed', [
                'technician_id' => $technician->id,
                'phone_number' => $technician->phone_number,
                'error' => $smsResult['error'] ?? 'Unknown error',
            ]);
        }

        // Add calculated attributes
        $technician->full_name = $technician->full_name;
        $technician->status_text = $technician->status_text;
        $technician->status_badge_class = $technician->status_badge_class;
        $technician->has_credentials = $technician->has_credentials;
        $technician->credentials_status_text = $technician->credentials_status_text;
        $technician->credentials_status_badge_class = $technician->credentials_status_badge_class;

        return response()->json([
            'message' => 'رمز عبور با موفقیت تنظیم شد',
            'data' => $technician->fresh(['organization', 'organizationUser'])
        ]);
    }

    /**
     * Get technician dashboard data
     */
    public function dashboard(Request $request, $id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $organizationId = $user->organization_id;
        
        $technician = Technician::where('organization_id', $organizationId)
            ->where('id', $id)
            ->with(['organization', 'organizationUser'])
            ->first();

        if (!$technician) {
            return response()->json(['message' => 'تکنیسین مورد نظر یافت نشد'], 404);
        }

        // Verify technician belongs to user's organization
        if ($technician->organization_id !== $organizationId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get all services for this technician with filters
        $servicesQuery = Service::with([
            'building' => function($query) {
                $query->with(['province', 'city']);
            },
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
        ->where('technician_id', $technician->id);

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

        // Apply building filter
        if ($request->has('building_id') && !empty($request->building_id)) {
            $servicesQuery->where('building_id', $request->building_id);
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

        // Calculate statistics
        $statistics = [
            'total_services' => $services->count(),
            'completed_services' => $services->where('status', Service::STATUS_COMPLETED)->count(),
            'pending_services' => $services->where('status', Service::STATUS_PENDING)->count(),
            'assigned_services' => $services->where('status', Service::STATUS_ASSIGNED)->count(),
            'expired_services' => $services->where('status', Service::STATUS_EXPIRED)->count(),
        ];

        // Get last completed service
        $lastService = $services->where('status', Service::STATUS_COMPLETED)
            ->sortByDesc('completed_at')
            ->first();

        $lastServiceData = null;
        if ($lastService && $lastService->completed_at) {
            $lastServiceJalali = Jalalian::forge($lastService->completed_at);
            $lastServiceDateJalali = $lastServiceJalali->format('Y/m/d');
            
            $today = Carbon::today();
            $lastServiceCarbon = Carbon::parse($lastService->completed_at)->startOfDay();
            $diffDays = $today->diffInDays($lastServiceCarbon, false);
            
            // Format the days text
            if ($diffDays === 0) {
                $daysSinceText = 'امروز';
            } elseif ($diffDays === 1) {
                $daysSinceText = 'دیروز';
            } elseif ($diffDays > 1) {
                $daysSinceText = $diffDays . ' روز پیش';
            } else {
                // Future date (shouldn't happen, but handle it)
                $daysSinceText = abs($diffDays) . ' روز بعد';
            }
            
            $monthNames = [
                1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
                5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
                9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند'
            ];
            $lastServiceMonthName = $monthNames[$lastServiceJalali->getMonth()] ?? $lastServiceJalali->getMonth();
            $lastServiceDateJalaliWithMonth = $lastServiceMonthName . ' ' . $lastServiceJalali->getDay() . '، ' . $lastServiceJalali->getYear();
            
            $lastServiceData = [
                'days_since_text' => $daysSinceText,
                'completed_at_jalali' => $lastServiceDateJalali,
                'completed_at_jalali_with_month' => $lastServiceDateJalaliWithMonth,
            ];
        }

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
                'building' => null,
                'checklist' => null,
            ];

            // Add building info
            if ($service->building) {
                $serviceData['building'] = [
                    'id' => $service->building->id,
                    'name' => $service->building->name,
                    'address' => $service->building->address,
                    'province' => $service->building->province ? $service->building->province->name : null,
                    'city' => $service->building->city ? $service->building->city->name : null,
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
        })->values();

        // Format technician data
        $technicianData = [
            'id' => $technician->id,
            'first_name' => $technician->first_name,
            'last_name' => $technician->last_name,
            'full_name' => $technician->full_name,
            'national_id' => $technician->national_id,
            'phone_number' => $technician->phone_number,
            'status' => $technician->status,
            'status_text' => $technician->status_text,
            'has_credentials' => $technician->has_credentials,
            'created_at' => $technician->created_at ? Jalalian::forge($technician->created_at)->format('Y/m/d') : null,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'technician' => $technicianData,
                'statistics' => $statistics,
                'last_service' => $lastServiceData,
                'services' => $formattedServices,
            ]
        ]);
    }
}
