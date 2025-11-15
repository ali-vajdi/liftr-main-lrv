<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;

class ViewController extends Controller
{
    public function showLogin()
    {
        return view('organization.auth');
    }

    public function showDashboard()
    {
        // Organization data will be fetched via API in the view
        return view('organization.dashboard');
    }

    public function showLockScreen()
    {
        return view('organization.auth-lockscreen');
    }

    public function showProfile()
    {
        // Organization data will be fetched via API in the view
        return view('organization.profile');
    }

    // Packages Management Views
    public function showPackages()
    {
        // Organization data will be fetched via API in the view
        return view('organization.packages.index');
    }

    // Users Management Views
    public function showUsers()
    {
        // Organization data will be fetched via API in the view
        return view('organization.users.index');
    }

    // Technicians Management Views
    public function showTechnicians()
    {
        // Organization data will be fetched via API in the view
        return view('organization.technicians.index');
    }

    // Buildings Management Views
    public function showBuildings()
    {
        // Organization data will be fetched via API in the view
        return view('organization.buildings.index');
    }

    public function showExpiringBuildings()
    {
        // Organization data will be fetched via API in the view
        return view('organization.buildings.expiring');
    }

    public function showBuildingElevators($buildingId)
    {
        // Building and organization data will be fetched via API in the view
        return view('organization.elevators.index', compact('buildingId'));
    }

    // Services Management Views
    public function showPendingServices()
    {
        // Organization data will be fetched via API in the view
        return view('organization.services.pending');
    }

    public function showAssignedServices()
    {
        // Organization data will be fetched via API in the view
        return view('organization.services.assigned');
    }

    public function showAllServices()
    {
        // Organization data will be fetched via API in the view
        return view('organization.services.all');
    }

    // Payment View
    public function showPayment()
    {
        return view('organization.payment.index');
    }
}


