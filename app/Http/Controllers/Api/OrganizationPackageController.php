<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationPackage;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class OrganizationPackageController extends Controller
{
    public function index(Request $request, $organizationId)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');

        $query = OrganizationPackage::where('organization_id', $organizationId)
            ->with(['package', 'moderator']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('package_name', 'like', "%{$search}%")
                  ->orWhere('package_duration_label', 'like', "%{$search}%");
            });
        }

        $organizationPackages = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Add calculated attributes to each item
        $items = $organizationPackages->items();
        foreach ($items as $item) {
            $item->remaining_days = $item->remaining_days;
            $item->expires_at = $item->expires_at;
            $item->status_text = $item->status_text;
            $item->status_badge_class = $item->status_badge_class;
            $item->formatted_price = $item->formatted_price;
            $item->is_expired = $item->is_expired;
            $item->has_package_changed = $item->has_package_changed;
            $item->assigned_package_info = $item->assigned_package_info;
            $item->current_package_info = $item->current_package_info;
        }

        return response()->json([
            'data' => $items,
            'pagination' => [
                'current_page' => $organizationPackages->currentPage(),
                'last_page' => $organizationPackages->lastPage(),
                'per_page' => $organizationPackages->perPage(),
                'total' => $organizationPackages->total(),
            ]
        ]);
    }

    public function store(Request $request, $organizationId)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
            'started_at' => 'nullable|date',
        ], [
            'package_id.required' => 'انتخاب پکیج الزامی است',
            'package_id.exists' => 'پکیج انتخاب شده معتبر نیست',
            'started_at.date' => 'تاریخ شروع باید معتبر باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $package = Package::findOrFail($request->package_id);
        $organization = Organization::findOrFail($organizationId);

        // Check if package is public
        if (!$package->is_public) {
            return response()->json([
                'message' => 'این پکیج برای عموم در دسترس نیست'
            ], 403);
        }

        // Calculate start and end dates
        $startedAt = $request->started_at ? Carbon::parse($request->started_at) : Carbon::now();
        
                // Check if organization has active packages
                $activePackages = $organization->activePackages();
                
                if ($activePackages->count() > 0) {
                    // If there are active packages, add remaining days from the one that expires first
                    $expiringSoonPackage = $activePackages->sortBy('expires_at')->first();
                    $remainingDays = $startedAt->diffInDays($expiringSoonPackage->expires_at, false);
                    $remainingDays = max(0, $remainingDays);
                } else {
                    $remainingDays = 0;
                }

        // Create new package assignment with stored package information
        $organizationPackage = OrganizationPackage::create([
            'organization_id' => $organizationId,
            'package_id' => $package->id,
            'package_name' => $package->name,
            'package_duration_days' => $package->duration_days + $remainingDays, // Add remaining days to duration
            'package_duration_label' => $package->duration_label,
            'package_price' => $package->price,
            'started_at' => $startedAt,
            'is_active' => true,
            'moderator_id' => Auth::id() ?? 1,
        ]);

        // Add calculated attributes
        $organizationPackage->remaining_days = $organizationPackage->remaining_days;
        $organizationPackage->status_text = $organizationPackage->status_text;
        $organizationPackage->status_badge_class = $organizationPackage->status_badge_class;
        $organizationPackage->formatted_price = $organizationPackage->formatted_price;
        $organizationPackage->is_expired = $organizationPackage->is_expired;
        $organizationPackage->has_package_changed = $organizationPackage->has_package_changed;
        $organizationPackage->assigned_package_info = $organizationPackage->assigned_package_info;
        $organizationPackage->current_package_info = $organizationPackage->current_package_info;

        return response()->json([
            'message' => 'پکیج با موفقیت اختصاص داده شد',
            'data' => $organizationPackage->load(['package', 'moderator'])
        ], 201);
    }

    public function show($organizationId, $id)
    {
        $organizationPackage = OrganizationPackage::where('organization_id', $organizationId)
            ->where('id', $id)
            ->with(['package', 'moderator', 'organization'])
            ->first();
        
        if (!$organizationPackage) {
            return response()->json([
                'message' => 'پکیج مورد نظر یافت نشد'
            ], 404);
        }
        
        // Add calculated attributes
        $organizationPackage->remaining_days = $organizationPackage->remaining_days;
        $organizationPackage->expires_at = $organizationPackage->expires_at;
        $organizationPackage->status_text = $organizationPackage->status_text;
        $organizationPackage->status_badge_class = $organizationPackage->status_badge_class;
        $organizationPackage->formatted_price = $organizationPackage->formatted_price;
        $organizationPackage->is_expired = $organizationPackage->is_expired;
        $organizationPackage->has_package_changed = $organizationPackage->has_package_changed;
        $organizationPackage->assigned_package_info = $organizationPackage->assigned_package_info;
        $organizationPackage->current_package_info = $organizationPackage->current_package_info;
        
        return response()->json([
            'data' => $organizationPackage
        ]);
    }

    public function update(Request $request, $organizationId, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_active' => 'required|in:true,false',
        ], [
            'is_active.required' => 'وضعیت فعال الزامی است',
            'is_active.in' => 'وضعیت فعال باید فعال یا غیرفعال باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $organizationPackage = OrganizationPackage::where('organization_id', $organizationId)
            ->where('id', $id)
            ->first();

        if (!$organizationPackage) {
            return response()->json([
                'message' => 'پکیج مورد نظر یافت نشد'
            ], 404);
        }

        $data = $request->all();
        $data['is_active'] = $data['is_active'] === 'true' || $data['is_active'] === true;
        
        $organizationPackage->update($data);

        // Add calculated attributes
        $organizationPackage->remaining_days = $organizationPackage->remaining_days;
        $organizationPackage->status_text = $organizationPackage->status_text;
        $organizationPackage->status_badge_class = $organizationPackage->status_badge_class;
        $organizationPackage->formatted_price = $organizationPackage->formatted_price;
        $organizationPackage->is_expired = $organizationPackage->is_expired;
        $organizationPackage->has_package_changed = $organizationPackage->has_package_changed;
        $organizationPackage->assigned_package_info = $organizationPackage->assigned_package_info;
        $organizationPackage->current_package_info = $organizationPackage->current_package_info;

        return response()->json([
            'message' => 'پکیج با موفقیت ویرایش شد',
            'data' => $organizationPackage->fresh(['package', 'moderator'])
        ]);
    }

    public function destroy($organizationId, $id)
    {
        $organizationPackage = OrganizationPackage::where('organization_id', $organizationId)
            ->where('id', $id)
            ->first();
        
        if (!$organizationPackage) {
            return response()->json([
                'message' => 'پکیج مورد نظر یافت نشد'
            ], 404);
        }
        
        $organizationPackage->delete();

        return response()->json([
            'message' => 'پکیج با موفقیت حذف شد'
        ]);
    }

    public function getAvailablePackages($organizationId)
    {
        $packages = Package::where('is_public', true)
            ->orderBy('price', 'asc')
            ->get();

        return response()->json([
            'data' => $packages
        ]);
    }

    public function getOrganizationCurrentPackage($organizationId)
    {
        $organization = Organization::findOrFail($organizationId);
        $activePackages = $organization->activePackages();
        
        // Add calculated attributes to each active package
        foreach ($activePackages as $package) {
            $package->load(['package', 'moderator']);
            $package->remaining_days = $package->remaining_days;
            $package->expires_at = $package->expires_at;
            $package->status_text = $package->status_text;
            $package->status_badge_class = $package->status_badge_class;
            $package->formatted_price = $package->formatted_price;
            $package->is_expired = $package->is_expired;
            $package->has_package_changed = $package->has_package_changed;
            $package->assigned_package_info = $package->assigned_package_info;
            $package->current_package_info = $package->current_package_info;
        }
        
        return response()->json([
            'data' => $activePackages,
            'total_remaining_days' => $organization->total_remaining_days,
            'count' => $activePackages->count()
        ]);
    }
}