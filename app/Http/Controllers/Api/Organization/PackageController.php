<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\OrganizationPackage;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        // Get organization ID from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $organizationId = $user->organization_id;
        
        $query = OrganizationPackage::where('organization_id', $organizationId)
            ->with(['package', 'moderator']);

        // Filtering and sorting
        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('package_name', 'like', '%' . $search . '%')
                    ->orWhere('package_duration_label', 'like', '%' . $search . '%')
                    ->orWhere('package_price', 'like', '%' . $search . '%');
            });
        }

        $sortField = $request->get('sort_field', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->get('per_page', 10);
        $organizationPackages = $query->paginate($perPage);

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
            'current_page' => $organizationPackages->currentPage(),
            'last_page' => $organizationPackages->lastPage(),
            'per_page' => $organizationPackages->perPage(),
            'total' => $organizationPackages->total(),
        ]);
    }

    public function show($id)
    {
        // Get organization ID from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $organizationId = $user->organization_id;
        
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
}
