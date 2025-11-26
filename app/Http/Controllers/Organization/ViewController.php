<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Building;

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

    public function showExpiredBuildings()
    {
        // Organization data will be fetched via API in the view
        return view('organization.buildings.expired');
    }

    public function showBuildingElevators(Building $building)
    {
        // Building and organization data will be fetched via API in the view
        $buildingId = $building->id;
        return view('organization.elevators.index', compact('buildingId'));
    }

    public function showBuildingDashboard(Building $building)
    {
        // Building and services data will be fetched via API in the view
        // Pass both ID and slug - use slug for API calls
        $buildingSlug = $building->slug;
        return view('organization.buildings.dashboard', compact('buildingSlug'));
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

    public function showCompletedServices()
    {
        // Organization data will be fetched via API in the view
        return view('organization.services.completed');
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

    // Messages Management Views
    public function showMessages()
    {
        return view('organization.messages.index');
    }

    public function showSentMessages()
    {
        return view('organization.messages.sent');
    }

    // Transactions Management View
    public function showTransactions()
    {
        return view('organization.transactions.index');
    }
}


