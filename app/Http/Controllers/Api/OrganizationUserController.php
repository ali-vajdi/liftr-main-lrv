<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganizationUser;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class OrganizationUserController extends Controller
{
    public function index(Request $request, $organizationId)
    {
        // Verify organization exists and user has access
        $organization = Organization::findOrFail($organizationId);
        
        $query = OrganizationUser::where('organization_id', $organizationId);

        // Handle search
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone_number', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('username', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Handle status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Handle created_at date range filters
        if ($request->has('created_at_from') && !empty($request->created_at_from)) {
            try {
                $jalaliDate = Jalalian::fromFormat('Y/m/d H:i:s', $request->created_at_from);
                $georgianDate = $jalaliDate->toCarbon()->format('Y-m-d');
                $query->whereDate('created_at', '>=', $georgianDate);
            } catch (\Exception $e) {
                // If date conversion fails, skip the filter
            }
        }

        if ($request->has('created_at_to') && !empty($request->created_at_to)) {
            try {
                $jalaliDate = Jalalian::fromFormat('Y/m/d H:i:s', $request->created_at_to);
                $georgianDate = $jalaliDate->toCarbon()->format('Y-m-d');
                $query->whereDate('created_at', '<=', $georgianDate);
            } catch (\Exception $e) {
                // If date conversion fails, skip the filter
            }
        }

        // Handle sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Get paginated results
        $perPage = $request->input('per_page', 10);
        $users = $query->paginate($perPage);

        return response()->json([
            'data' => $users->items(),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
            'organization' => $organization
        ]);
    }

    public function store(Request $request, $organizationId)
    {
        // Verify organization exists
        $organization = Organization::findOrFail($organizationId);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'username' => 'nullable|string|max:255|unique:organization_users,username',
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:true,false',
        ], [
            'name.required' => 'نام کاربر الزامی است',
            'name.max' => 'نام کاربر نمی‌تواند بیش از 255 کاراکتر باشد',
            'phone_number.required' => 'شماره تلفن الزامی است',
            'phone_number.max' => 'شماره تلفن نمی‌تواند بیش از 20 کاراکتر باشد',
            'username.unique' => 'این نام کاربری قبلاً استفاده شده است',
            'username.max' => 'نام کاربری نمی‌تواند بیش از 255 کاراکتر باشد',
            'password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد',
            'status.required' => 'وضعیت الزامی است',
            'status.in' => 'وضعیت باید فعال یا غیرفعال باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['organization_id'] = $organizationId;
        $data['moderator_id'] = Auth::id();
        $data['status'] = $data['status'] === 'true' || $data['status'] === true;

        $user = OrganizationUser::create($data);

        return response()->json([
            'message' => 'کاربر سازمان با موفقیت ایجاد شد',
            'data' => $user
        ], 201);
    }

    public function show($organizationId, $id)
    {
        $user = OrganizationUser::where('organization_id', $organizationId)->findOrFail($id);
        
        return response()->json([
            'data' => $user
        ]);
    }

    public function update(Request $request, $organizationId, $id)
    {
        $user = OrganizationUser::where('organization_id', $organizationId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'username' => 'nullable|string|max:255|unique:organization_users,username,' . $id,
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:true,false',
        ], [
            'name.required' => 'نام کاربر الزامی است',
            'name.max' => 'نام کاربر نمی‌تواند بیش از 255 کاراکتر باشد',
            'phone_number.required' => 'شماره تلفن الزامی است',
            'phone_number.max' => 'شماره تلفن نمی‌تواند بیش از 20 کاراکتر باشد',
            'username.unique' => 'این نام کاربری قبلاً استفاده شده است',
            'username.max' => 'نام کاربری نمی‌تواند بیش از 255 کاراکتر باشد',
            'password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد',
            'status.required' => 'وضعیت الزامی است',
            'status.in' => 'وضعیت باید فعال یا غیرفعال باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['status'] = $data['status'] === 'true' || $data['status'] === true;

        // Don't update password if it's empty
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'کاربر سازمان با موفقیت ویرایش شد',
            'data' => $user
        ]);
    }

    public function destroy($organizationId, $id)
    {
        $user = OrganizationUser::where('organization_id', $organizationId)->findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'کاربر سازمان با موفقیت حذف شد'
        ]);
    }

    public function setCredentials(Request $request, $organizationId, $id)
    {
        $user = OrganizationUser::where('organization_id', $organizationId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:organization_users,username,' . $id,
            'password' => 'required|string|min:6',
        ], [
            'username.required' => 'نام کاربری الزامی است',
            'username.unique' => 'این نام کاربری قبلاً استفاده شده است',
            'username.max' => 'نام کاربری نمی‌تواند بیش از 255 کاراکتر باشد',
            'password.required' => 'رمز عبور الزامی است',
            'password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update([
            'username' => $request->username,
            'password' => $request->password,
        ]);

        return response()->json([
            'message' => 'نام کاربری و رمز عبور با موفقیت تنظیم شد',
            'data' => $user
        ]);
    }
}
