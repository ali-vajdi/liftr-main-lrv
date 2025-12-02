<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use App\Models\TechnicianOtpVerification;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TechnicianAuthController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->middleware('auth:technician_api')->except(['login', 'sendOtp', 'verifyOtp']);
        $this->smsService = $smsService;
    }

    /**
     * Login with phone number and password
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'password' => 'required|string',
        ]);

        $technician = Technician::where('phone_number', $request->phone_number)
            ->where('status', true) // Only active technicians
            ->first();

        if (!$technician) {
            return response()->json([
                'message' => 'شماره تماس یا رمز عبور اشتباه است.'
            ], 401);
        }

        // Check if password is set
        if (empty($technician->password)) {
            return response()->json([
                'message' => 'رمز عبور برای این تکنسین تعریف نشده است.'
            ], 403);
        }

        // Check if password is a valid Bcrypt hash (starts with $2y$, $2a$, or $2b$)
        $isBcryptHash = preg_match('/^\$2[ayb]\$.{56}$/', $technician->password);
        
        if (!$isBcryptHash) {
            // Password is not properly hashed - check if it matches as plain text (for migration)
            if ($technician->password === $request->password) {
                // Rehash the password properly
                $technician->password = Hash::make($request->password);
                $technician->save();
            } else {
                return response()->json([
                    'message' => 'شماره تماس یا رمز عبور اشتباه است.'
                ], 401);
            }
        } else {
            // Password is properly hashed - verify it
            if (!Hash::check($request->password, $technician->password)) {
                return response()->json([
                    'message' => 'شماره تماس یا رمز عبور اشتباه است.'
                ], 401);
            }
        }

        $token = $technician->createToken('technician-token')->accessToken;

        return response()->json([
            'token' => $token,
            'technician' => $technician->load(['organization', 'organizationUser']),
            'message' => 'ورود با موفقیت انجام شد.'
        ]);
    }

    /**
     * Send OTP to phone number using SMS pattern
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
        ]);

        $technician = Technician::where('phone_number', $request->phone_number)
            ->where('status', true) // Only active technicians
            ->first();

        if (!$technician) {
            return response()->json([
                'message' => 'تکنسین با این شماره تماس یافت نشد.'
            ], 404);
        }

        // Create OTP verification
        $otpVerification = TechnicianOtpVerification::createOtp($request->phone_number);

        // Get organization from technician
        $organization = $technician->organization;
        
        if (!$organization) {
            Log::error('Technician OTP: Organization not found', [
                'technician_id' => $technician->id,
                'phone_number' => $request->phone_number,
            ]);
            
            return response()->json([
                'message' => 'خطا در ارسال پیامک. سازمان یافت نشد.',
            ], 500);
        }

        // Send SMS with technician welcome pattern via queue (async)
        $result = $this->smsService->sendTechnicianWelcomeSms(
            $organization,
            $request->phone_number,
            $otpVerification->otp_code,
            true // Use queue
        );

        if (!$result['success']) {
            Log::error('Technician OTP SMS failed', [
                'technician_id' => $technician->id,
                'phone_number' => $request->phone_number,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
        }

        // Always return success to prevent OTP code disclosure
        // In production, don't return the OTP code even in debug mode
        return response()->json([
            'message' => 'کد تایید ارسال شد.',
        ]);
    }

    /**
     * Verify OTP and login
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'otp_code' => 'required|string|size:6',
        ]);

        $technician = Technician::where('phone_number', $request->phone_number)
            ->where('status', true) // Only active technicians
            ->first();

        if (!$technician) {
            return response()->json([
                'message' => 'تکنسین با این شماره تماس یافت نشد.'
            ], 404);
        }

        // Verify OTP
        if (!TechnicianOtpVerification::verifyOtp($request->phone_number, $request->otp_code)) {
            return response()->json([
                'message' => 'کد تایید نامعتبر یا منقضی شده است.'
            ], 401);
        }

        $token = $technician->createToken('technician-token')->accessToken;

        return response()->json([
            'token' => $token,
            'technician' => $technician->load(['organization', 'organizationUser']),
            'message' => 'ورود با موفقیت انجام شد.'
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        
        return response()->json([
            'message' => 'خروج با موفقیت انجام شد.'
        ]);
    }

    /**
     * Check authentication status
     */
    public function checkAuth(Request $request)
    {
        return response()->json([
            'authenticated' => true,
            'technician' => $request->user()->load(['organization', 'organizationUser'])
        ]);
    }

    /**
     * Get technician profile
     */
    public function profile(Request $request)
    {
        $technician = $request->user()->load(['organization', 'organizationUser']);
        
        return response()->json([
            'data' => [
                'id' => $technician->id,
                'first_name' => $technician->first_name,
                'last_name' => $technician->last_name,
                'full_name' => $technician->full_name,
                'phone_number' => $technician->phone_number,
                'national_id' => $technician->national_id,
                'organization_id' => $technician->organization_id,
                'organization_name' => $technician->organization ? $technician->organization->name : null,
                'status' => $technician->status,
                'organization' => $technician->organization ? [
                    'id' => $technician->organization->id,
                    'name' => $technician->organization->name,
                ] : null,
            ]
        ]);
    }
}
