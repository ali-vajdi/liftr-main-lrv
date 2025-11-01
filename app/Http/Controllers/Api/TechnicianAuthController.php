<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use App\Models\TechnicianOtpVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TechnicianAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:technician_api')->except(['login', 'sendOtp', 'verifyOtp']);
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

        if (!$technician || !Hash::check($request->password, $technician->password)) {
            return response()->json([
                'message' => 'شماره تماس یا رمز عبور اشتباه است.'
            ], 401);
        }

        // Check if technician has credentials
        if (!$technician->has_credentials) {
            return response()->json([
                'message' => 'اعتبارنامه برای این تکنسین تعریف نشده است.'
            ], 403);
        }

        $token = $technician->createToken('technician-token')->accessToken;

        return response()->json([
            'token' => $token,
            'technician' => $technician->load(['organization', 'organizationUser']),
            'message' => 'ورود با موفقیت انجام شد.'
        ]);
    }

    /**
     * Send OTP to phone number
     * TODO: Connect to SMS panel here
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

        // TODO: Send SMS with OTP code
        // Example: $this->sendSms($request->phone_number, $otpVerification->otp_code);
        // 
        // Placeholder for SMS panel integration:
        // You should implement this method to connect to your SMS panel
        // For now, we'll just return success (in production, you should send the SMS)
        
        // For development/testing purposes only - remove in production:
        // In production, implement SMS sending here and don't return the OTP code
        return response()->json([
            'message' => 'کد تایید ارسال شد.',
            // Remove this in production - only for testing
            'otp_code' => config('app.debug') ? $otpVerification->otp_code : null,
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
                'organization' => $technician->organization ? [
                    'id' => $technician->organization->id,
                    'name' => $technician->organization->name,
                ] : null,
            ]
        ]);
    }
}
