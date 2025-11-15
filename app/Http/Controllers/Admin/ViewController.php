<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class ViewController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth');
    }

    public function showDashboard()
    {
        return view('admin.dashboard');
    }

    public function showLockScreen()
    {
        return view('admin.auth-lockscreen');
    }


    // Moderators Management Views
    public function showModerators()
    {
        return view('admin.moderators.index');
    }

    public function showProfile()
    {
        return view('admin.profile');
    }

    // Organizations Management Views
    public function showOrganizations()
    {
        return view('admin.organizations.index');
    }

    public function showOrganizationUsers($organizationId)
    {
        $organization = \App\Models\Organization::findOrFail($organizationId);
        return view('admin.organizations.users.index', compact('organization'));
    }

    // Packages Management Views
    public function showPackages()
    {
        return view('admin.packages.index');
    }

    public function showOrganizationPackages($organizationId)
    {
        $organization = \App\Models\Organization::findOrFail($organizationId);
        return view('admin.organizations.packages.index', compact('organization'));
    }

    // Unit Checklists Management Views
    public function showUnitChecklists()
    {
        return view('admin.unit-checklists.index');
    }

    // Description Checklists Management Views
    public function showDescriptionChecklists()
    {
        return view('admin.description-checklists.index');
    }

    // SMS Management Views
    public function showSms()
    {
        return view('admin.sms.index');
    }

    // Accounting/Transactions Management Views
    public function showTransactions()
    {
        return view('admin.transactions.index');
    }

} 