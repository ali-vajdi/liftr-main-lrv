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

} 