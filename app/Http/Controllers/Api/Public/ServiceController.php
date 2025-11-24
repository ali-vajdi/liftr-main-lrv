<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
}

