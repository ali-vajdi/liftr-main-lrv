<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Sms;
use Illuminate\Http\Request;

class SmsController extends Controller
{
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

        return response()->json([
            'data' => [
                'total' => $totalSms,
                'sent' => $sentSms,
                'pending' => $pendingSms,
                'failed' => $failedSms,
            ]
        ]);
    }
}
