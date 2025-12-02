<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Sms;
use App\Models\Organization;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Get SMS statistics
     */
    public function statistics(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $organizationId = $user->organization_id;

        $totalSms = Sms::where('organization_id', $organizationId)->count();
        $sentSms = Sms::where('organization_id', $organizationId)->where('status', 'sent')->count();
        $pendingSms = Sms::where('organization_id', $organizationId)->where('status', 'pending')->count();
        $failedSms = Sms::where('organization_id', $organizationId)->where('status', 'failed')->count();

        $organization = Organization::find($organizationId);

        return response()->json([
            'data' => [
                'total' => $totalSms,
                'sent' => $sentSms,
                'pending' => $pendingSms,
                'failed' => $failedSms,
                'balance' => (float) ($organization->sms_balance ?? 0),
                'cost_per_message' => (float) ($organization->sms_cost_per_message ?? 0),
            ]
        ]);
    }

    /**
     * Get available SMS patterns
     */
    public function getPatterns(Request $request)
    {
        $patterns = $this->smsService->getAvailablePatterns();

        return response()->json([
            'data' => $patterns
        ]);
    }

    /**
     * Get pattern details by code
     */
    public function getPattern(Request $request, $code)
    {
        $pattern = $this->smsService->getPattern($code);

        if (!$pattern) {
            return response()->json([
                'message' => 'الگو یافت نشد'
            ], 404);
        }

        return response()->json([
            'data' => $pattern
        ]);
    }

}
