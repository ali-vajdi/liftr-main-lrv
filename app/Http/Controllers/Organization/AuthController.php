<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\OrganizationUser;
use App\Models\Organization;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:organization_api')->except(['login', 'unlockScreen', 'showForgotPassword', 'forgotPassword', 'showResetPassword', 'resetPassword']);
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

        // Load organization to check its status
        $organizationUser->load('organization');
        
        // Check if organization is disabled
        if (!$organizationUser->organization || !$organizationUser->organization->status) {
            return response()->json([
                'message' => 'سازمان شما غیرفعال است. لطفا با پشتیبانی تماس بگیرید.'
            ], 403);
        }

        $token = $organizationUser->createToken('organization-token')->accessToken;

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
        
        // Load organization to check its status
        $organizationUser->load('organization');
        
        // Check if organization is disabled
        if (!$organizationUser->organization || !$organizationUser->organization->status) {
            return response()->json([
                'message' => 'سازمان شما غیرفعال است. لطفا با پشتیبانی تماس بگیرید.'
            ], 403);
        }
        
        // Generate a new token for the user
        $token = $organizationUser->createToken('organization-token')->accessToken;
        
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
        
        // Check if organization is disabled
        if (!$user->organization || !$user->organization->status) {
            // Revoke the token to force logout
            $user->token()->revoke();
            
            return response()->json([
                'authenticated' => false,
                'message' => 'سازمان شما غیرفعال است. لطفا با پشتیبانی تماس بگیرید.',
                'organization_disabled' => true
            ], 403);
        }
        
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
                    'landline_phone' => $user->organization->landline_phone,
                    'logo' => $user->organization->logo,
                    'status' => $user->organization->status,
                    'contract_number_format' => $user->organization->contract_number_format,
                    'contract_number_increment' => $user->organization->contract_number_increment,
                    'invoice_number_increment' => $user->organization->invoice_number_increment,
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

    public function getOrganization(Request $request)
    {
        $user = $request->user();
        $organization = $user->organization;

        if (!$organization) {
            return response()->json(['message' => 'سازمان یافت نشد'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'address' => $organization->address,
                'landline_phone' => $organization->landline_phone,
                'logo' => $organization->logo,
                'status' => $organization->status,
                'contract_number_format' => $organization->contract_number_format,
                'contract_number_increment' => $organization->contract_number_increment,
                'invoice_number_increment' => $organization->invoice_number_increment,
                'created_at' => $organization->created_at,
                'updated_at' => $organization->updated_at,
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
            'address' => 'nullable|string',
            'landline_phone' => 'nullable|string|max:20|regex:/^[0-9]+$/',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'logo.image' => 'فایل انتخاب شده باید تصویر باشد',
            'logo.mimes' => 'فرمت تصویر باید JPG یا PNG باشد',
            'logo.max' => 'حجم تصویر نمی‌تواند بیش از 2 مگابایت باشد',
            'landline_phone.regex' => 'تلفن ثابت باید فقط شامل اعداد باشد',
            'landline_phone.max' => 'تلفن ثابت نمی‌تواند بیش از 20 کاراکتر باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Explicitly exclude name from updates - organizations cannot change their name
        $data = $request->only(['address', 'landline_phone']);

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

    /**
     * Show forgot password page
     */
    public function showForgotPassword()
    {
        return view('organization.forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
        ], [
            'phone_number.required' => 'شماره تلفن الزامی است',
        ]);

        $user = OrganizationUser::where('phone_number', $request->phone_number)
            ->where('status', true)
            ->first();

        // Always return success message for security (don't reveal if user exists)
        if (!$user) {
            return response()->json([
                'message' => 'اگر شماره تلفن شما در سیستم موجود باشد، لینک بازنشانی رمز عبور به شما ارسال خواهد شد.'
            ], 200);
        }

        // Generate reset token (shorter for SMS compatibility - max 40 chars after domain)
        // Target: /reset-password/{token}?p={encoded_phone} <= 40 chars
        // /reset-password/ = 16 chars, ?p= = 3 chars, encoded phone ~15 chars
        // So token can be max: 40 - 16 - 3 - 15 = 6 chars (too short for security)
        // Using shorter route /rp/ = 4 chars instead
        // New calculation: /rp/ = 4, ?p= = 3, encoded phone ~15, so token max = 40 - 4 - 3 - 15 = 18 chars
        $token = Str::random(18);
        $expiresAt = now()->addHours(2); // 2 hours validity

        // Store token in database
        DB::table('organization_password_reset_tokens')->updateOrInsert(
            ['phone_number' => $user->phone_number],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Generate reset URL - encode phone number in base64url format to shorten it
        // Format: /rp/{token}?p={base64url_encoded_phone}
        // This keeps the URL path + query under 40 characters
        $encodedPhone = rtrim(strtr(base64_encode($user->phone_number), '+/', '-_'), '=');
        $resetUrl = '/rp/' . $token . '?p=' . $encodedPhone;

        // Send SMS
        $smsService = new SmsService();
        $smsResult = $smsService->sendOrganizationPasswordResetSms(
            $user->organization,
            $user->phone_number,
            $user->name,
            $resetUrl,
            true // Queue the SMS sending
        );

        if (!$smsResult['success']) {
            return response()->json([
                'message' => 'خطا در ارسال پیامک. لطفا دوباره تلاش کنید.',
                'error' => $smsResult['error'] ?? 'Unknown error'
            ], 500);
        }

        return response()->json([
            'message' => 'اگر شماره تلفن شما در سیستم موجود باشد، لینک بازنشانی رمز عبور به شما ارسال خواهد شد.'
        ], 200);
    }

    /**
     * Show reset password page
     */
    public function showResetPassword(Request $request, $token)
    {
        // Decode phone number from base64url encoded 'p' parameter
        $encodedPhone = $request->query('p');
        if (!$encodedPhone) {
            return redirect()->route('organization.forgot-password')
                ->with('error', 'لینک نامعتبر است.');
        }
        
        // Decode base64url to get phone number
        $phoneNumber = base64_decode(strtr($encodedPhone, '-_', '+/'));
        
        if (!$phoneNumber) {
            return redirect()->route('organization.forgot-password')
                ->with('error', 'لینک نامعتبر است.');
        }

        // Verify token
        $resetToken = DB::table('organization_password_reset_tokens')
            ->where('phone_number', $phoneNumber)
            ->first();

        if (!$resetToken) {
            return redirect()->route('organization.forgot-password')
                ->with('error', 'لینک بازنشانی رمز عبور نامعتبر یا منقضی شده است.');
        }

        // Check if token is expired (2 hours)
        $tokenAge = now()->diffInHours($resetToken->created_at);
        if ($tokenAge >= 2) {
            DB::table('organization_password_reset_tokens')
                ->where('phone_number', $phoneNumber)
                ->delete();
            
            return redirect()->route('organization.forgot-password')
                ->with('error', 'لینک بازنشانی رمز عبور منقضی شده است. لطفا دوباره درخواست دهید.');
        }

        // Verify token hash
        if (!Hash::check($token, $resetToken->token)) {
            return redirect()->route('organization.forgot-password')
                ->with('error', 'لینک بازنشانی رمز عبور نامعتبر است.');
        }

        return view('organization.reset-password', [
            'token' => $token,
            'phone_number' => $phoneNumber
        ]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'phone_number' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'token.required' => 'توکن الزامی است',
            'phone_number.required' => 'شماره تلفن الزامی است',
            'password.required' => 'رمز عبور الزامی است',
            'password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد',
            'password.confirmed' => 'رمز عبور و تکرار آن مطابقت ندارند',
        ]);

        // Verify token
        $resetToken = DB::table('organization_password_reset_tokens')
            ->where('phone_number', $request->phone_number)
            ->first();

        if (!$resetToken) {
            return response()->json([
                'message' => 'لینک بازنشانی رمز عبور نامعتبر یا منقضی شده است.'
            ], 422);
        }

        // Check if token is expired (2 hours)
        $tokenAge = now()->diffInHours($resetToken->created_at);
        if ($tokenAge >= 2) {
            DB::table('organization_password_reset_tokens')
                ->where('phone_number', $request->phone_number)
                ->delete();
            
            return response()->json([
                'message' => 'لینک بازنشانی رمز عبور منقضی شده است. لطفا دوباره درخواست دهید.'
            ], 422);
        }

        // Verify token hash
        if (!Hash::check($request->token, $resetToken->token)) {
            return response()->json([
                'message' => 'لینک بازنشانی رمز عبور نامعتبر است.'
            ], 422);
        }

        // Find user
        $user = OrganizationUser::where('phone_number', $request->phone_number)
            ->where('status', true)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'کاربر یافت نشد.'
            ], 404);
        }

        // Update password
        $user->password = $request->password;
        $user->save();

        // Delete reset token
        DB::table('organization_password_reset_tokens')
            ->where('phone_number', $request->phone_number)
            ->delete();

        return response()->json([
            'message' => 'رمز عبور با موفقیت تغییر یافت. اکنون می‌توانید با رمز عبور جدید وارد شوید.'
        ], 200);
    }

    /**
     * Update contract settings
     */
    public function updateContractSettings(Request $request)
    {
        $user = $request->user();
        $organization = $user->organization;

        if (!$organization) {
            return response()->json(['message' => 'سازمان یافت نشد'], 404);
        }

        $validator = Validator::make($request->all(), [
            'contract_number_format' => 'required|array',
            'contract_number_format.parts' => 'required|array|min:1',
            'contract_number_format.parts.*' => 'required|in:increment,day,day_name,month,month_number,year,text',
            'contract_number_format.separators' => 'nullable|array',
            'contract_number_format.separators.*' => 'nullable|in:/,-',
            'contract_number_format.custom_text' => 'nullable|string|max:255',
        ], [
            'contract_number_format.required' => 'قالب شماره فاکتور الزامی است',
            'contract_number_format.parts.required' => 'بخش‌های قالب الزامی است',
            'contract_number_format.parts.min' => 'حداقل یک بخش باید انتخاب شود',
            'contract_number_format.parts.*.in' => 'نوع بخش نامعتبر است',
            'contract_number_format.separators.*.in' => 'جداکننده باید / یا - باشد',
            'contract_number_format.custom_text.max' => 'متن سفارشی نمی‌تواند بیش از 255 کاراکتر باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validate that if text part exists, custom_text must be provided
        if (in_array('text', $request->contract_number_format['parts']) && empty($request->contract_number_format['custom_text'])) {
            return response()->json([
                'errors' => [
                    'contract_number_format.custom_text' => ['برای بخش متن، متن سفارشی الزامی است']
                ]
            ], 422);
        }

        // Validate separators count (should be parts.length - 1)
        $partsCount = count($request->contract_number_format['parts']);
        $separatorsCount = count($request->contract_number_format['separators'] ?? []);
        if ($separatorsCount !== $partsCount - 1 && $partsCount > 1) {
            return response()->json([
                'errors' => [
                    'contract_number_format.separators' => ['تعداد جداکننده‌ها باید یک عدد کمتر از تعداد بخش‌ها باشد']
                ]
            ], 422);
        }

        $organization->contract_number_format = $request->contract_number_format;
        $organization->save();

        return response()->json([
            'message' => 'تنظیمات قرارداد با موفقیت بروزرسانی شد',
            'data' => [
                'contract_number_format' => $organization->contract_number_format,
                'contract_number_increment' => $organization->contract_number_increment,
            ]
        ]);
    }

}
