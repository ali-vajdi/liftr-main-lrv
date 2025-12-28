<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Get Faraz SMS balance from API
     *
     * @return array|null
     */
    private function getFarazSmsBalance(): ?array
    {
        try {
            $apiUrl = config('services.sms.api_url') . '/api/payment/credit/mine';
            $token = config('services.sms.token');

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ])
                ->get($apiUrl);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['meta']['status']) && $responseData['meta']['status'] === true) {
                return $responseData['data'] ?? null;
            }

            Log::warning('Failed to fetch Faraz SMS balance', [
                'http_status' => $response->status(),
                'response' => $responseData,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error fetching Faraz SMS balance', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get admin dashboard data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardData()
    {
        // Get Faraz SMS balance
        $farazSmsBalance = $this->getFarazSmsBalance();

        // Get organizations statistics by status
        $organizationsStats = [
            'total' => Organization::count(),
            'active' => Organization::where('status', true)->count(),
            'inactive' => Organization::where('status', false)->count(),
        ];

        // Get total application users (OrganizationUser)
        $applicationUsersTotal = OrganizationUser::count();

        // Get total remaining SMS credit of all organizations
        $totalSmsCredit = Organization::sum('sms_balance') ?? 0;

        // Get statistics
        $statistics = [
            'organizations' => $organizationsStats,
            'application_users' => [
                'total' => $applicationUsersTotal,
            ],
            'total_sms_credit' => (float) $totalSmsCredit,
        ];

        return response()->json([
            'data' => [
                'faraz_sms_balance' => $farazSmsBalance,
                'statistics' => $statistics,
            ],
        ]);
    }
} 