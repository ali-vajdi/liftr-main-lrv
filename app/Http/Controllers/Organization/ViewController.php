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
        $user = auth('organization')->user();
        if (!$user) {
            // If no authenticated user, get first organization for display
            $organization = Organization::first();
        } else {
            $organization = $user->organization;
        }
        
        return view('organization.dashboard', compact('organization'));
    }

    public function showLockScreen()
    {
        return view('organization.auth-lockscreen');
    }

    public function showProfile()
    {
        // Get organization from authenticated user
        $user = auth('organization')->user();
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
        $user = auth('organization')->user();
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
        $user = auth('organization')->user();
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
        $user = auth('organization')->user();
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
        $user = auth('organization')->user();
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
        $user = auth('organization')->user();
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
        $user = auth('organization')->user();
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
        $user = auth('organization')->user();
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
        $user = auth('organization')->user();
        if (!$user) {
            // If no authenticated user, get first organization for display
            $organization = Organization::first();
        } else {
            $organization = $user->organization;
        }
        
        return view('organization.services.assigned', compact('organization'));
    }
}
