<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\OrganizationUser;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Get organization ID from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $organizationId = $user->organization_id;
        
        $query = OrganizationUser::where('organization_id', $organizationId);

        // Filtering and sorting
        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone_number', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%');
            });
        }

        $sortField = $request->get('sort_field', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->get('per_page', 10);
        $organizationUsers = $query->paginate($perPage);

        // Add calculated attributes to each item
        $items = $organizationUsers->items();
        foreach ($items as $item) {
            $item->status_text = $item->status_text;
            $item->status_badge_class = $item->status_badge_class;
        }

        return response()->json([
            'data' => $items,
            'current_page' => $organizationUsers->currentPage(),
            'last_page' => $organizationUsers->lastPage(),
            'per_page' => $organizationUsers->perPage(),
            'total' => $organizationUsers->total(),
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
        
        $organizationUser = OrganizationUser::where('organization_id', $organizationId)
            ->where('id', $id)
            ->with(['organization', 'moderator'])
            ->first();
        
        if (!$organizationUser) {
            return response()->json([
                'message' => 'کاربر مورد نظر یافت نشد'
            ], 404);
        }
        
        // Add calculated attributes
        $organizationUser->status_text = $organizationUser->status_text;
        $organizationUser->status_badge_class = $organizationUser->status_badge_class;
        
        return response()->json([
            'data' => $organizationUser
        ]);
    }
}
