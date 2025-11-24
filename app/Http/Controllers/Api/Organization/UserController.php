<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\OrganizationUser;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
                    ->orWhere('phone_number', 'like', '%' . $search . '%');
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

    public function store(Request $request)
    {
        // Get organization ID from authenticated user
        $authUser = auth('organization_api')->user();
        if (!$authUser) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $organizationId = $authUser->organization_id;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:organization_users,phone_number',
            'password' => 'nullable|string|min:6',
            'status' => 'required',
        ], [
            'name.required' => 'نام کاربر الزامی است',
            'name.max' => 'نام کاربر نمی‌تواند بیش از 255 کاراکتر باشد',
            'phone_number.required' => 'شماره تلفن الزامی است',
            'phone_number.max' => 'شماره تلفن نمی‌تواند بیش از 20 کاراکتر باشد',
            'phone_number.unique' => 'این شماره تلفن قبلاً استفاده شده است',
            'password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد',
            'status.required' => 'وضعیت الزامی است',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get moderator_id from the organization
        $organization = Organization::findOrFail($organizationId);
        $moderatorId = $organization->moderator_id;

        $data = $request->all();
        $data['organization_id'] = $organizationId;
        $data['moderator_id'] = $moderatorId;
        $data['status'] = $request->boolean('status', true);

        $user = OrganizationUser::create($data);

        // Add calculated attributes
        $user->status_text = $user->status_text;
        $user->status_badge_class = $user->status_badge_class;

        return response()->json([
            'message' => 'کاربر شرکت با موفقیت ایجاد شد',
            'data' => $user
        ], 201);
    }
}
