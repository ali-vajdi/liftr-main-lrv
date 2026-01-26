<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Damage;
use App\Models\Building;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class DamageController extends Controller
{
    /**
     * Get all damages for the organization
     */
    public function index(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $query = Damage::with(['building', 'technician'])
            ->where('organization_id', $user->organization_id);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('building', function($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('technician', function($tq) use ($search) {
                      $tq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by building
        if ($request->has('building_id') && $request->building_id) {
            $query->where('building_id', $request->building_id);
        }

        // Filter by technician
        if ($request->has('technician_id') && $request->technician_id) {
            $query->where('technician_id', $request->technician_id);
        }

        // Sorting
        $sortField = $request->get('sort_field', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->get('per_page', 10);
        $damages = $query->paginate($perPage);

        $items = collect($damages->items())->map(function ($damage) {
            return [
                'id' => $damage->id,
                'building_id' => $damage->building_id,
                'building' => $damage->building ? [
                    'id' => $damage->building->id,
                    'name' => $damage->building->name,
                ] : null,
                'technician_id' => $damage->technician_id,
                'technician' => $damage->technician ? [
                    'id' => $damage->technician->id,
                    'first_name' => $damage->technician->first_name,
                    'last_name' => $damage->technician->last_name,
                    'name' => $damage->technician->first_name . ' ' . $damage->technician->last_name,
                ] : null,
                'report_date' => $damage->report_date ? $damage->report_date->toIso8601String() : null,
                'report_date_jalali' => $damage->report_date_jalali,
                'visit_date' => $damage->visit_date ? $damage->visit_date->toIso8601String() : null,
                'visit_date_jalali' => $damage->visit_date_jalali,
                'description' => $damage->description,
                'created_at' => $damage->created_at->toIso8601String(),
                'created_at_jalali' => Jalalian::forge($damage->created_at)->format('Y/m/d H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'current_page' => $damages->currentPage(),
                'last_page' => $damages->lastPage(),
                'per_page' => $damages->perPage(),
                'total' => $damages->total(),
                'from' => $damages->firstItem(),
                'to' => $damages->lastItem(),
            ]
        ]);
    }

    /**
     * Get a single damage
     */
    public function show(Damage $damage)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify damage belongs to the organization
        if ($damage->organization_id !== $user->organization_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $damage->load(['building', 'technician']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $damage->id,
                'building_id' => $damage->building_id,
                'building' => $damage->building ? [
                    'id' => $damage->building->id,
                    'name' => $damage->building->name,
                ] : null,
                'technician_id' => $damage->technician_id,
                'technician' => $damage->technician ? [
                    'id' => $damage->technician->id,
                    'first_name' => $damage->technician->first_name,
                    'last_name' => $damage->technician->last_name,
                    'name' => $damage->technician->first_name . ' ' . $damage->technician->last_name,
                ] : null,
                'report_date' => $damage->report_date ? $damage->report_date->toIso8601String() : null,
                'report_date_jalali' => $damage->report_date_jalali,
                'visit_date' => $damage->visit_date ? $damage->visit_date->toIso8601String() : null,
                'visit_date_jalali' => $damage->visit_date_jalali,
                'description' => $damage->description,
                'created_at' => $damage->created_at->toIso8601String(),
                'created_at_jalali' => Jalalian::forge($damage->created_at)->format('Y/m/d H:i'),
            ]
        ]);
    }

    /**
     * Create a new damage report
     */
    public function store(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'building_id' => 'required|exists:buildings,id',
            'report_date' => 'required|date',
            'visit_date' => 'nullable|date',
            'technician_id' => 'nullable|exists:technicians,id',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی داده‌ها',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify building belongs to organization
        $building = Building::where('id', $request->building_id)
            ->where('organization_id', $user->organization_id)
            ->first();

        if (!$building) {
            return response()->json([
                'success' => false,
                'message' => 'ساختمان یافت نشد'
            ], 404);
        }

        // Verify technician belongs to organization if provided
        if ($request->technician_id) {
            $technician = Technician::where('id', $request->technician_id)
                ->where('organization_id', $user->organization_id)
                ->first();

            if (!$technician) {
                return response()->json([
                    'success' => false,
                    'message' => 'تکنسین یافت نشد'
                ], 404);
            }
        }

        // Convert Jalali date to Gregorian if needed
        $reportDate = $request->report_date;
        if (preg_match('/^\d{4}\/\d{2}\/\d{2}/', $reportDate)) {
            // Jalali date format (YYYY/MM/DD HH:mm)
            try {
                $jalaliDate = Jalalian::fromFormat('Y/m/d H:i', $reportDate);
                $reportDate = $jalaliDate->toCarbon();
            } catch (\Exception $e) {
                // Try without time if format fails
                try {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $reportDate);
                    $reportDate = $jalaliDate->toCarbon();
                } catch (\Exception $e2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'فرمت تاریخ اعلام نامعتبر است',
                        'errors' => ['report_date' => ['فرمت تاریخ باید YYYY/MM/DD HH:mm باشد']]
                    ], 422);
                }
            }
        } else {
            $reportDate = Carbon::parse($reportDate);
        }

        $visitDate = null;
        if ($request->visit_date) {
            $visitDate = $request->visit_date;
            if (preg_match('/^\d{4}\/\d{2}\/\d{2}/', $visitDate)) {
                // Jalali date format (YYYY/MM/DD HH:mm)
                try {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d H:i', $visitDate);
                    $visitDate = $jalaliDate->toCarbon();
                } catch (\Exception $e) {
                    // Try without time if format fails
                    try {
                        $jalaliDate = Jalalian::fromFormat('Y/m/d', $visitDate);
                        $visitDate = $jalaliDate->toCarbon();
                    } catch (\Exception $e2) {
                        return response()->json([
                            'success' => false,
                            'message' => 'فرمت تاریخ مراجعه نامعتبر است',
                            'errors' => ['visit_date' => ['فرمت تاریخ باید YYYY/MM/DD HH:mm باشد']]
                        ], 422);
                    }
                }
            } else {
                $visitDate = Carbon::parse($visitDate);
            }
        }

        $damage = Damage::create([
            'organization_id' => $user->organization_id,
            'building_id' => $request->building_id,
            'technician_id' => $request->technician_id,
            'report_date' => $reportDate,
            'visit_date' => $visitDate,
            'description' => $request->description,
        ]);

        $damage->load(['building', 'technician']);

        return response()->json([
            'success' => true,
            'message' => 'گزارش خرابی با موفقیت ثبت شد',
            'data' => [
                'id' => $damage->id,
                'building_id' => $damage->building_id,
                'building' => $damage->building ? [
                    'id' => $damage->building->id,
                    'name' => $damage->building->name,
                ] : null,
                'technician_id' => $damage->technician_id,
                'technician' => $damage->technician ? [
                    'id' => $damage->technician->id,
                    'first_name' => $damage->technician->first_name,
                    'last_name' => $damage->technician->last_name,
                    'name' => $damage->technician->first_name . ' ' . $damage->technician->last_name,
                ] : null,
                'report_date' => $damage->report_date ? $damage->report_date->toIso8601String() : null,
                'report_date_jalali' => $damage->report_date_jalali,
                'visit_date' => $damage->visit_date ? $damage->visit_date->toIso8601String() : null,
                'visit_date_jalali' => $damage->visit_date_jalali,
                'description' => $damage->description,
            ]
        ], 201);
    }

    /**
     * Update a damage report
     */
    public function update(Request $request, Damage $damage)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify damage belongs to the organization
        if ($damage->organization_id !== $user->organization_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'building_id' => 'required|exists:buildings,id',
            'report_date' => 'required|date',
            'visit_date' => 'nullable|date',
            'technician_id' => 'nullable|exists:technicians,id',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی داده‌ها',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify building belongs to organization
        $building = Building::where('id', $request->building_id)
            ->where('organization_id', $user->organization_id)
            ->first();

        if (!$building) {
            return response()->json([
                'success' => false,
                'message' => 'ساختمان یافت نشد'
            ], 404);
        }

        // Verify technician belongs to organization if provided
        if ($request->technician_id) {
            $technician = Technician::where('id', $request->technician_id)
                ->where('organization_id', $user->organization_id)
                ->first();

            if (!$technician) {
                return response()->json([
                    'success' => false,
                    'message' => 'تکنسین یافت نشد'
                ], 404);
            }
        }

        // Convert Jalali date to Gregorian if needed
        $reportDate = $request->report_date;
        if (preg_match('/^\d{4}\/\d{2}\/\d{2}/', $reportDate)) {
            // Jalali date format (YYYY/MM/DD HH:mm)
            try {
                $jalaliDate = Jalalian::fromFormat('Y/m/d H:i', $reportDate);
                $reportDate = $jalaliDate->toCarbon();
            } catch (\Exception $e) {
                // Try without time if format fails
                try {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $reportDate);
                    $reportDate = $jalaliDate->toCarbon();
                } catch (\Exception $e2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'فرمت تاریخ اعلام نامعتبر است',
                        'errors' => ['report_date' => ['فرمت تاریخ باید YYYY/MM/DD HH:mm باشد']]
                    ], 422);
                }
            }
        } else {
            $reportDate = Carbon::parse($reportDate);
        }

        $visitDate = null;
        if ($request->visit_date) {
            $visitDate = $request->visit_date;
            if (preg_match('/^\d{4}\/\d{2}\/\d{2}/', $visitDate)) {
                // Jalali date format (YYYY/MM/DD HH:mm)
                try {
                    $jalaliDate = Jalalian::fromFormat('Y/m/d H:i', $visitDate);
                    $visitDate = $jalaliDate->toCarbon();
                } catch (\Exception $e) {
                    // Try without time if format fails
                    try {
                        $jalaliDate = Jalalian::fromFormat('Y/m/d', $visitDate);
                        $visitDate = $jalaliDate->toCarbon();
                    } catch (\Exception $e2) {
                        return response()->json([
                            'success' => false,
                            'message' => 'فرمت تاریخ مراجعه نامعتبر است',
                            'errors' => ['visit_date' => ['فرمت تاریخ باید YYYY/MM/DD HH:mm باشد']]
                        ], 422);
                    }
                }
            } else {
                $visitDate = Carbon::parse($visitDate);
            }
        }

        $damage->update([
            'building_id' => $request->building_id,
            'technician_id' => $request->technician_id,
            'report_date' => $reportDate,
            'visit_date' => $visitDate,
            'description' => $request->description,
        ]);

        $damage->load(['building', 'technician']);

        return response()->json([
            'success' => true,
            'message' => 'گزارش خرابی با موفقیت به‌روزرسانی شد',
            'data' => [
                'id' => $damage->id,
                'building_id' => $damage->building_id,
                'building' => $damage->building ? [
                    'id' => $damage->building->id,
                    'name' => $damage->building->name,
                ] : null,
                'technician_id' => $damage->technician_id,
                'technician' => $damage->technician ? [
                    'id' => $damage->technician->id,
                    'first_name' => $damage->technician->first_name,
                    'last_name' => $damage->technician->last_name,
                    'name' => $damage->technician->first_name . ' ' . $damage->technician->last_name,
                ] : null,
                'report_date' => $damage->report_date ? $damage->report_date->toIso8601String() : null,
                'report_date_jalali' => $damage->report_date_jalali,
                'visit_date' => $damage->visit_date ? $damage->visit_date->toIso8601String() : null,
                'visit_date_jalali' => $damage->visit_date_jalali,
                'description' => $damage->description,
            ]
        ]);
    }

    /**
     * Delete a damage report
     */
    public function destroy(Damage $damage)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify damage belongs to the organization
        if ($damage->organization_id !== $user->organization_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $damage->delete();

        return response()->json([
            'success' => true,
            'message' => 'گزارش خرابی با موفقیت حذف شد'
        ]);
    }

    /**
     * Get buildings for damage form
     */
    public function getBuildings()
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $buildings = Building::where('organization_id', $user->organization_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $buildings
        ]);
    }
}

