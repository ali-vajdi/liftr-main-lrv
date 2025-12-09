<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\PdfVerificationCode;
use App\Models\Service;
use App\Services\SmsService;
use App\Services\SmsPattern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * Update user note for an assigned service
     *
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserNote(Request $request, Service $service)
    {
        $validator = Validator::make($request->all(), [
            'user_note' => 'nullable|string|max:5000',
        ], [
            'user_note.max' => 'یادداشت نمی‌تواند بیشتر از 5000 کاراکتر باشد.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی',
                'errors' => $validator->errors()
            ], 422);
        }

        // Ensure service is assigned
        if ($service->status !== Service::STATUS_ASSIGNED) {
            return response()->json([
                'success' => false,
                'message' => 'فقط می‌توانید یادداشت را برای سرویس‌های اختصاص داده شده ثبت کنید.'
            ], 400);
        }

        // Update user note
        $service->update([
            'user_note' => $request->user_note ? trim($request->user_note) : null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'یادداشت با موفقیت ذخیره شد.',
            'data' => [
                'user_note' => $service->user_note
            ]
        ]);
    }

    /**
     * Send verification code for PDF download
     *
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendPdfVerificationCode(Request $request, Service $service)
    {
        // Only allow for completed services
        if ($service->status !== Service::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'فقط سرویس‌های تکمیل شده قابل دانلود هستند.'
            ], 400);
        }

        // Load checklist relationship
        $service->load('checklist.elevatorChecklists');

        // Check if service has checklist
        if (!$service->checklist || $service->checklist->elevatorChecklists->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'چک لیست برای این سرویس موجود نیست.'
            ], 400);
        }

        // Load building with organization
        $service->load('building.organization');
        $building = $service->building;

        // Check if building has manager phone
        if (!$building->manager_phone) {
            return response()->json([
                'success' => false,
                'message' => 'شماره تماس مدیر ساختمان ثبت نشده است.'
            ], 400);
        }

        // Generate 6-digit code
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Invalidate any existing unexpired codes for this service and IP
        PdfVerificationCode::where('service_id', $service->id)
            ->where('ip_address', $request->ip())
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->update(['used' => true]);

        // Create verification code record in database
        $verificationCode = PdfVerificationCode::create([
            'service_id' => $service->id,
            'code' => $code,
            'ip_address' => $request->ip(),
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send SMS (queued)
        $smsService = new SmsService();
        $patternCode = SmsPattern::getPatternCode('pdf_download_code');
        
        if (!$patternCode) {
            // Delete the verification code if SMS pattern is not found
            $verificationCode->delete();
            return response()->json([
                'success' => false,
                'message' => 'خطا در ارسال پیامک. لطفا با پشتیبانی تماس بگیرید.'
            ], 500);
        }

        // Queue SMS sending
        $smsResult = $smsService->sendPatternSms(
            $building->organization,
            $patternCode,
            ['code' => $code],
            $building->manager_phone,
            true // Queue the SMS
        );

        if (!$smsResult['success']) {
            // Delete the verification code if SMS queuing failed
            $verificationCode->delete();
            return response()->json([
                'success' => false,
                'message' => $smsResult['message'] ?? 'خطا در ارسال پیامک. لطفا دوباره تلاش کنید.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'کد تایید به شماره مدیر ساختمان ارسال شد.'
        ]);
    }

    /**
     * Verify code and return download token
     *
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyPdfCode(Request $request, Service $service)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
        ], [
            'code.required' => 'لطفا کد تایید را وارد کنید.',
            'code.size' => 'کد تایید باید 6 رقم باشد.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی',
                'errors' => $validator->errors()
            ], 422);
        }

        // Only allow for completed services
        if ($service->status !== Service::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'فقط سرویس‌های تکمیل شده قابل دانلود هستند.'
            ], 400);
        }

        // Load checklist relationship
        $service->load('checklist.elevatorChecklists');

        // Check if service has checklist
        if (!$service->checklist || $service->checklist->elevatorChecklists->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'چک لیست برای این سرویس موجود نیست.'
            ], 400);
        }

        // Find verification code in database
        $verificationCode = PdfVerificationCode::where('service_id', $service->id)
            ->where('ip_address', $request->ip())
            ->where('code', $request->code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verificationCode) {
            return response()->json([
                'success' => false,
                'message' => 'کد تایید نامعتبر است یا منقضی شده است.'
            ], 400);
        }

        // Mark code as verified
        $verificationCode->update([
            'verified' => true,
            'verified_at' => now(),
        ]);

        // Generate download token (valid for 5 minutes)
        $downloadToken = Str::random(64);
        $verificationCode->update([
            'download_token' => $downloadToken,
            'expires_at' => now()->addMinutes(5), // Extend expiration for download token
        ]);

        return response()->json([
            'success' => true,
            'message' => 'کد تایید صحیح است.',
            'data' => [
                'token' => $downloadToken
            ]
        ]);
    }
}

