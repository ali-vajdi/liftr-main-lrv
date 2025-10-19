<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use App\Models\OrganizationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TechnicianController extends Controller
{
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
                    ->orWhere('phone_number', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%');
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
            'username' => 'nullable|string|max:255|unique:technicians,username',
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:true,false',
        ], [
            'first_name.required' => 'نام الزامی است',
            'last_name.required' => 'نام خانوادگی الزامی است',
            'national_id.required' => 'کد ملی الزامی است',
            'national_id.unique' => 'کد ملی تکراری است',
            'phone_number.required' => 'شماره تماس الزامی است',
            'username.unique' => 'نام کاربری تکراری است',
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

        $technician = Technician::create($data);

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
            'username' => 'nullable|string|max:255|unique:technicians,username,' . $id,
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:true,false',
        ], [
            'first_name.required' => 'نام الزامی است',
            'last_name.required' => 'نام خانوادگی الزامی است',
            'national_id.required' => 'کد ملی الزامی است',
            'national_id.unique' => 'کد ملی تکراری است',
            'phone_number.required' => 'شماره تماس الزامی است',
            'username.unique' => 'نام کاربری تکراری است',
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
        
        // Only update password if provided
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $technician->update($data);

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
            'username' => 'required|string|max:255|unique:technicians,username,' . $id,
            'password' => 'required|string|min:6',
        ], [
            'username.required' => 'نام کاربری الزامی است',
            'username.unique' => 'نام کاربری تکراری است',
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

        $technician->update([
            'username' => $request->username,
            'password' => $request->password,
        ]);

        // Add calculated attributes
        $technician->full_name = $technician->full_name;
        $technician->status_text = $technician->status_text;
        $technician->status_badge_class = $technician->status_badge_class;
        $technician->has_credentials = $technician->has_credentials;
        $technician->credentials_status_text = $technician->credentials_status_text;
        $technician->credentials_status_badge_class = $technician->credentials_status_badge_class;

        return response()->json([
            'message' => 'اطلاعات ورود با موفقیت تنظیم شد',
            'data' => $technician->fresh(['organization', 'organizationUser'])
        ]);
    }
}
