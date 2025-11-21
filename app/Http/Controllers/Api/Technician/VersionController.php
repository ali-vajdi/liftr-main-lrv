<?php

namespace App\Http\Controllers\Api\Technician;

use App\Http\Controllers\Controller;
use App\Models\ApplicationVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VersionController extends Controller
{
    /**
     * Check if application has update available
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'platform' => 'required|in:web,android',
            'version' => 'required|string|max:50',
        ], [
            'platform.required' => 'پلتفرم الزامی است',
            'platform.in' => 'پلتفرم باید وب یا اندروید باشد',
            'version.required' => 'نسخه فعلی الزامی است',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $platform = $request->input('platform');
        $currentVersion = $request->input('version');

        // Get the latest version for the platform
        $latestVersion = ApplicationVersion::where('platform', $platform)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latestVersion) {
            return response()->json([
                'success' => true,
                'has_update' => false,
                'force_update' => false,
                'message' => 'نسخه‌ای در سیستم ثبت نشده است'
            ]);
        }

        // Compare versions (simple string comparison, you might want to use version_compare for semantic versioning)
        $hasUpdate = version_compare($latestVersion->version, $currentVersion, '>');

        return response()->json([
            'success' => true,
            'has_update' => $hasUpdate,
            'force_update' => $hasUpdate && $latestVersion->force_update,
            'latest_version' => $latestVersion->version,
            'current_version' => $currentVersion,
            'description' => $latestVersion->description,
            'platform' => $latestVersion->platform,
        ]);
    }
}
