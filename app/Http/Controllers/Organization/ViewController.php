<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Organization;

class ViewController extends Controller
{
    public function showLogin()
    {
        return view('organization.auth');
    }

    public function showDashboard()
    {
        // Get organization from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            // If no authenticated user, get first organization for display
            $organization = Organization::first();
        } else {
            $organization = $user->organization;
        }
        
        // Calculate comprehensive statistics
        $statistics = $this->calculateStatistics($organization);
        
        return view('organization.dashboard', compact('organization', 'statistics'));
    }

    private function calculateStatistics($organization)
    {
        // SMS Statistics
        $smsStats = [
            'balance' => $organization->sms_balance ?? 0,
            'total' => \App\Models\Sms::where('organization_id', $organization->id)->count(),
            'sent' => \App\Models\Sms::where('organization_id', $organization->id)->where('status', 'sent')->count(),
            'pending' => \App\Models\Sms::where('organization_id', $organization->id)->where('status', 'pending')->count(),
        ];

        // Current Package
        $currentPackage = $organization->activePackage();

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

        return [
            'sms' => $smsStats,
            'current_package' => $currentPackage,
            'users' => $userStats,
            'technicians' => $technicianStats,
            'buildings' => $buildingStats,
            'services' => $serviceStats,
        ];
    }

    public function showLockScreen()
    {
        return view('organization.auth-lockscreen');
    }

    public function showProfile()
    {
        // Get organization from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            // If no authenticated user, get first organization for display
            $organization = Organization::first();
        } else {
            $organization = $user->organization;
        }
        
        return view('organization.profile', compact('organization'));
    }

    // Packages Management Views
    public function showPackages()
    {
        // Get organization from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            // If no authenticated user, get first organization for display
            $organization = Organization::first();
        } else {
            $organization = $user->organization;
        }
        
        return view('organization.packages.index', compact('organization'));
    }

    // Users Management Views
    public function showUsers()
    {
        // Get organization from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            // If no authenticated user, get first organization for display
            $organization = Organization::first();
        } else {
            $organization = $user->organization;
        }
        
        return view('organization.users.index', compact('organization'));
    }

    // Technicians Management Views
    public function showTechnicians()
    {
        // Get organization from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            // If no authenticated user, get first organization for display
            $organization = Organization::first();
        } else {
            $organization = $user->organization;
        }
        
        return view('organization.technicians.index', compact('organization'));
    }

    // Buildings Management Views
    public function showBuildings()
    {
        // Get organization from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            // If no authenticated user, get first organization for display
            $organization = Organization::first();
        } else {
            $organization = $user->organization;
        }
        
        return view('organization.buildings.index', compact('organization'));
    }

    public function showExpiringBuildings()
    {
        // Get organization from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            // If no authenticated user, get first organization for display
            $organization = Organization::first();
        } else {
            $organization = $user->organization;
        }
        
        return view('organization.buildings.expiring', compact('organization'));
    }

    public function showBuildingElevators($buildingId)
    {
        // Get organization from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            // If no authenticated user, get first organization for display
            $organization = Organization::first();
        } else {
            $organization = $user->organization;
        }

        // Get building and verify it belongs to organization
        $building = Building::where('organization_id', $organization->id)
            ->findOrFail($buildingId);
        
        return view('organization.elevators.index', compact('organization', 'building'));
    }

    // Services Management Views
    public function showPendingServices()
    {
        // Get organization from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            // If no authenticated user, get first organization for display
            $organization = Organization::first();
        } else {
            $organization = $user->organization;
        }
        
        return view('organization.services.pending', compact('organization'));
    }

    public function showAssignedServices()
    {
        // Get organization from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            // If no authenticated user, get first organization for display
            $organization = Organization::first();
        } else {
            $organization = $user->organization;
        }
        
        return view('organization.services.assigned', compact('organization'));
    }

    public function showAllServices()
    {
        // Get organization from authenticated user
        $user = auth('organization_api')->user();
        if (!$user) {
            // If no authenticated user, get first organization for display
            $organization = Organization::first();
        } else {
            $organization = $user->organization;
        }
        
        return view('organization.services.all', compact('organization'));
    }
}
