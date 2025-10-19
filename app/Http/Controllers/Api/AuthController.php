<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Moderator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['login', 'unlockScreen']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $moderator = Moderator::where('username', $request->username)->first();

        if (!$moderator || !Hash::check($request->password, $moderator->password)) {
            return response()->json([
                'message' => 'نام کاربری یا رمز عبور اشتباه است.'
            ], 401);
        }

        $token = $moderator->createToken('admin-token')->accessToken;

        return response()->json([
            'token' => $token,
            'user' => $moderator,
            'message' => 'ورود با موفقیت انجام شد.'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        
        return response()->json([
            'message' => 'خروج با موفقیت انجام شد.'
        ]);
    }

    public function lockScreen(Request $request)
    {
        // We'll handle the lock state in the frontend with localStorage
        return response()->json([
            'message' => 'صفحه قفل شد.'
        ]);
    }

    public function unlockScreen(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'username' => 'required',
        ]);

        $moderator = Moderator::where('username', $request->username)->first();
        
        if (!$moderator || !Hash::check($request->password, $moderator->password)) {
            return response()->json([
                'message' => 'رمز عبور اشتباه است.'
            ], 401);
        }
        
        // Generate a new token for the user
        $token = $moderator->createToken('admin-token')->accessToken;
        
        return response()->json([
            'message' => 'قفل صفحه باز شد.',
            'token' => $token,
            'user' => $moderator
        ]);
    }
    
    public function checkAuth(Request $request)
    {
        return response()->json([
            'authenticated' => true,
            'user' => $request->user()
        ]);
    }
} 