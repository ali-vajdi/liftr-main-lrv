<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\OrganizationUser;
use App\Models\Organization;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
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
        
        // Find the first user (main user) - user with minimum ID
        $firstUser = OrganizationUser::where('organization_id', $organizationId)
            ->orderBy('id', 'asc')
            ->first();
        $firstUserId = $firstUser ? $firstUser->id : null;
        
        // Check if current user is the main user
        $currentUserIsMain = $user->id === $firstUserId;
        
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
            // Mark if this is the main user (first user)
            $item->is_main_user = ($item->id === $firstUserId);
        }

        return response()->json([
            'data' => $items,
            'current_page' => $organizationUsers->currentPage(),
            'last_page' => $organizationUsers->lastPage(),
            'per_page' => $organizationUsers->perPage(),
            'total' => $organizationUsers->total(),
            'current_user_is_main' => $currentUserIsMain,
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
        
        // Find the first user (main user)
        $firstUser = OrganizationUser::where('organization_id', $organizationId)
            ->orderBy('id', 'asc')
            ->first();
        $firstUserId = $firstUser ? $firstUser->id : null;
        
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
        $organizationUser->is_main_user = ($organizationUser->id === $firstUserId);
        
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
            'password' => 'required|string|min:6',
            'status' => 'required',
        ], [
            'name.required' => 'نام کاربر الزامی است',
            'name.max' => 'نام کاربر نمی‌تواند بیش از 255 کاراکتر باشد',
            'phone_number.required' => 'شماره تلفن الزامی است',
            'phone_number.max' => 'شماره تلفن نمی‌تواند بیش از 20 کاراکتر باشد',
            'phone_number.unique' => 'این شماره تلفن قبلاً استفاده شده است',
            'password.required' => 'رمز عبور الزامی است',
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

        // Store plain password before hashing (for SMS)
        $plainPassword = $data['password'];

        $user = OrganizationUser::create($data);

        // Always send SMS with user credentials
        $smsResult = $this->smsService->sendOrganizationUserWelcomeSms(
            $organization,
            $user->phone_number,
            $user->name,
            $plainPassword,
            true // Use queue
        );

        if (!$smsResult['success']) {
            Log::error('Organization user welcome SMS failed', [
                'user_id' => $user->id,
                'phone_number' => $user->phone_number,
                'error' => $smsResult['error'] ?? 'Unknown error',
            ]);
        }

        // Add calculated attributes
        $user->status_text = $user->status_text;
        $user->status_badge_class = $user->status_badge_class;

        // Add calculated attributes
        $user->status_text = $user->status_text;
        $user->status_badge_class = $user->status_badge_class;

        return response()->json([
            'message' => 'کاربر شرکت با موفقیت ایجاد شد',
            'data' => $user
        ], 201);
    }

    public function update(Request $request, $id)
    {
        // Get organization ID from authenticated user
        $authUser = auth('organization_api')->user();
        if (!$authUser) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $organizationId = $authUser->organization_id;
        
        // Find the first user (main user)
        $firstUser = OrganizationUser::where('organization_id', $organizationId)
            ->orderBy('id', 'asc')
            ->first();
        $firstUserId = $firstUser ? $firstUser->id : null;
        
        // Only the main user can update other users
        if ($authUser->id !== $firstUserId) {
            return response()->json([
                'message' => 'شما اجازه ویرایش کاربران را ندارید'
            ], 403);
        }
        
        $organizationUser = OrganizationUser::where('organization_id', $organizationId)
            ->where('id', $id)
            ->first();
        
        if (!$organizationUser) {
            return response()->json([
                'message' => 'کاربر مورد نظر یافت نشد'
            ], 404);
        }
        
        // Prevent updating the main user's status to inactive
        if ($id === $firstUserId && $request->has('status') && !$request->boolean('status')) {
            return response()->json([
                'message' => 'نمی‌توانید وضعیت مدیر عامل را غیرفعال کنید'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone_number' => 'sometimes|required|string|max:20|unique:organization_users,phone_number,' . $id,
            'password' => 'sometimes|nullable|string|min:6',
            'status' => 'sometimes|required',
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

        $data = $request->only(['name', 'phone_number', 'status']);
        
        // Only update password if provided
        if ($request->has('password') && $request->password) {
            $data['password'] = $request->password;
        }
        
        if ($request->has('status')) {
            $data['status'] = $request->boolean('status');
        }

        $organizationUser->update($data);

        // Add calculated attributes
        $organizationUser->status_text = $organizationUser->status_text;
        $organizationUser->status_badge_class = $organizationUser->status_badge_class;
        $organizationUser->is_main_user = ($organizationUser->id === $firstUserId);

        return response()->json([
            'message' => 'کاربر با موفقیت به‌روزرسانی شد',
            'data' => $organizationUser
        ]);
    }

    public function destroy($id)
    {
        // Get organization ID from authenticated user
        $authUser = auth('organization_api')->user();
        if (!$authUser) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $organizationId = $authUser->organization_id;
        
        // Find the first user (main user)
        $firstUser = OrganizationUser::where('organization_id', $organizationId)
            ->orderBy('id', 'asc')
            ->first();
        $firstUserId = $firstUser ? $firstUser->id : null;
        
        // Only the main user can delete other users
        if ($authUser->id !== $firstUserId) {
            return response()->json([
                'message' => 'شما اجازه حذف کاربران را ندارید'
            ], 403);
        }
        
        $organizationUser = OrganizationUser::where('organization_id', $organizationId)
            ->where('id', $id)
            ->first();
        
        if (!$organizationUser) {
            return response()->json([
                'message' => 'کاربر مورد نظر یافت نشد'
            ], 404);
        }
        
        // Prevent deleting the main user
        if ($id === $firstUserId) {
            return response()->json([
                'message' => 'نمی‌توانید مدیر عامل را حذف کنید'
            ], 422);
        }

        $organizationUser->delete();

        return response()->json([
            'message' => 'کاربر با موفقیت حذف شد'
        ]);
    }
}
