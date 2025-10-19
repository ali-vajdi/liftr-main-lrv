<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:moderator')->except(['logout', 'showLockScreen', 'unlockScreen']);
    }

    public function showLogin()
    {
        if (Auth::guard('moderator')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::guard('moderator')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'username' => 'نام کاربری یا رمز عبور اشتباه است.',
        ])->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        Auth::guard('moderator')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function showLockScreen()
    {
        if (!Auth::guard('moderator')->check()) {
            return redirect()->route('admin.login');
        }
        
        if (!session()->has('locked')) {
            session(['locked' => true]);
        }
        
        $user = Auth::guard('moderator')->user();
        return view('admin.auth-lockscreen', compact('user'));
    }

    public function unlockScreen(Request $request)
    {
        if (!Auth::guard('moderator')->check()) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'password' => 'required',
        ]);

        $user = Auth::guard('moderator')->user();
        
        if (Hash::check($request->password, $user->password)) {
            session()->forget('locked');
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'password' => 'رمز عبور اشتباه است.',
        ]);
    }
} 