<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getDashboardData(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $organization = $user->organization;
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        // SMS Statistics
        $smsStats = [
            'balance' => (float) ($organization->sms_balance ?? 0),
            'total' => \App\Models\Sms::where('organization_id', $organization->id)->count(),
            'sent' => \App\Models\Sms::where('organization_id', $organization->id)->where('status', 'sent')->count(),
            'pending' => \App\Models\Sms::where('organization_id', $organization->id)->where('status', 'pending')->count(),
        ];

        // Current Package
        $currentPackage = $organization->activePackage();
        $packageData = null;
        if ($currentPackage) {
            $packageData = [
                'id' => $currentPackage->id,
                'package_name' => $currentPackage->package_name,
                'package_duration_label' => $currentPackage->package_duration_label,
                'remaining_days' => $currentPackage->remaining_days,
                'status_badge_class' => $currentPackage->status_badge_class,
                'expires_at' => $currentPackage->expires_at->toISOString(),
            ];
        }

        // User Statistics
        $userStats = [
            'total' => $organization->users()->count(),
            'active' => $organization->users()->where('status', true)->count(),
        ];

        // Technician Statistics
        $technicianStats = [
            'total' => \App\Models\Technician::where('organization_id', $organization->id)->count(),
            'active' => \App\Models\Technician::where('organization_id', $organization->id)->where('status', true)->count(),
        ];

        // Building Statistics
        $buildingStats = [
            'total' => \App\Models\Building::where('organization_id', $organization->id)->count(),
            'active' => \App\Models\Building::where('organization_id', $organization->id)->where('status', true)->count(),
            'expiring_soon' => \App\Models\Building::where('organization_id', $organization->id)
                ->where('service_end_date', '<=', now()->addDays(30))
                ->where('service_end_date', '>=', now())
                ->count(),
        ];

        // Service Statistics
        $serviceStats = [
            'total' => \App\Models\Service::whereHas('building', function($query) use ($organization) {
                $query->where('organization_id', $organization->id);
            })->count(),
            'pending' => \App\Models\Service::whereHas('building', function($query) use ($organization) {
                $query->where('organization_id', $organization->id);
            })->where('status', 'pending')->count(),
            'assigned' => \App\Models\Service::whereHas('building', function($query) use ($organization) {
                $query->where('organization_id', $organization->id);
            })->where('status', 'assigned')->count(),
            'completed' => \App\Models\Service::whereHas('building', function($query) use ($organization) {
                $query->where('organization_id', $organization->id);
            })->where('status', 'completed')->count(),
            'expired' => \App\Models\Service::whereHas('building', function($query) use ($organization) {
                $query->where('organization_id', $organization->id);
            })->where('status', 'expired')->count(),
        ];

        return response()->json([
            'data' => [
                'organization' => [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'address' => $organization->address,
                    'logo' => $organization->logo,
                    'status' => $organization->status,
                    'sms_balance' => (float) ($organization->sms_balance ?? 0),
                ],
                'statistics' => [
                    'sms' => $smsStats,
                    'current_package' => $packageData,
                    'users' => $userStats,
                    'technicians' => $technicianStats,
                    'buildings' => $buildingStats,
                    'services' => $serviceStats,
                ]
            ]
        ]);
    }
}

