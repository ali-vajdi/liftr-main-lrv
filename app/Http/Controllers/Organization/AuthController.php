<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\OrganizationUser;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:organization_api')->except(['login', 'unlockScreen']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'password' => 'required',
        ]);

        $organizationUser = OrganizationUser::where('phone_number', $request->phone_number)
            ->where('status', true) // Only active users
            ->first();

        if (!$organizationUser || !Hash::check($request->password, $organizationUser->password)) {
            return response()->json([
                'message' => 'شماره تلفن یا رمز عبور اشتباه است.'
            ], 401);
        }

        $token = $organizationUser->createToken('organization-token')->accessToken;
        $organizationUser->load('organization');

        return response()->json([
            'token' => $token,
            'user' => $organizationUser,
            'message' => 'ورود با موفقیت انجام شد.'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        
        return response()->json([
            'message' => 'خروج با موفقیت انجام شد.'
        ]);
    }

    public function lockScreen(Request $request)
    {
        // We'll handle the lock state in the frontend with localStorage
        return response()->json([
            'message' => 'صفحه قفل شد.'
        ]);
    }

    public function unlockScreen(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'phone_number' => 'required',
        ]);

        $organizationUser = OrganizationUser::where('phone_number', $request->phone_number)
            ->where('status', true) // Only active users
            ->first();
        
        if (!$organizationUser || !Hash::check($request->password, $organizationUser->password)) {
            return response()->json([
                'message' => 'رمز عبور اشتباه است.'
            ], 401);
        }
        
        // Generate a new token for the user
        $token = $organizationUser->createToken('organization-token')->accessToken;
        $organizationUser->load('organization');
        
        return response()->json([
            'message' => 'قفل صفحه باز شد.',
            'token' => $token,
            'user' => $organizationUser
        ]);
    }
    
    public function checkAuth(Request $request)
    {
        $user = $request->user();
        $user->load('organization');
        
        return response()->json([
            'authenticated' => true,
            'user' => $user
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load('organization');
        
        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'status' => $user->status,
                'organization' => $user->organization ? [
                    'id' => $user->organization->id,
                    'name' => $user->organization->name,
                    'address' => $user->organization->address,
                    'logo' => $user->organization->logo,
                    'status' => $user->organization->status,
                    'created_at' => $user->organization->created_at,
                    'updated_at' => $user->organization->updated_at,
                ] : null,
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'current_password' => 'required_with:new_password|string',
            'new_password' => 'nullable|string|min:6|confirmed',
        ], [
            'name.required' => 'نام الزامی است',
            'name.max' => 'نام نمی‌تواند بیش از 255 کاراکتر باشد',
            'current_password.required_with' => 'برای تغییر رمز عبور، رمز عبور فعلی را وارد کنید',
            'new_password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد',
            'new_password.confirmed' => 'رمز عبور جدید و تکرار آن مطابقت ندارند',
        ]);

        // Verify current password if changing password
        if (isset($validated['current_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['message' => 'رمز عبور فعلی اشتباه است'], 422);
            }
        }

        $user->name = $validated['name'];

        if (isset($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        return response()->json([
            'message' => 'پروفایل با موفقیت بروزرسانی شد',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'status' => $user->status,
            ]
        ]);
    }

    public function updateOrganization(Request $request)
    {
        $user = $request->user();
        $organization = $user->organization;

        if (!$organization) {
            return response()->json(['message' => 'سازمان یافت نشد'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'name.required' => 'نام شرکت الزامی است',
            'name.max' => 'نام شرکت نمی‌تواند بیش از 255 کاراکتر باشد',
            'logo.image' => 'فایل انتخاب شده باید تصویر باشد',
            'logo.mimes' => 'فرمت تصویر باید JPG یا PNG باشد',
            'logo.max' => 'حجم تصویر نمی‌تواند بیش از 2 مگابایت باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['name', 'address']);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($organization->logo) {
                $oldLogoPath = str_replace('/storage/', 'public/', $organization->logo);
                Storage::delete($oldLogoPath);
            }
            
            $logo = $request->file('logo');
            $logoName = time() . '_' . $logo->getClientOriginalName();
            $logoPath = $logo->storeAs('public/organization_logos', $logoName);
            $data['logo'] = Storage::url($logoPath);
        }

        $organization->update($data);

        return response()->json([
            'message' => 'اطلاعات شرکت با موفقیت بروزرسانی شد',
            'data' => $organization
        ]);
    }
}
