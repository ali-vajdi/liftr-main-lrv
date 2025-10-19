<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\OrganizationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:organization_api')->except(['login', 'unlockScreen']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $organizationUser = OrganizationUser::where('username', $request->username)
            ->where('status', true) // Only active users
            ->first();

        if (!$organizationUser || !Hash::check($request->password, $organizationUser->password)) {
            return response()->json([
                'message' => 'نام کاربری یا رمز عبور اشتباه است.'
            ], 401);
        }

        $token = $organizationUser->createToken('organization-token')->accessToken;

        return response()->json([
            'token' => $token,
            'user' => $organizationUser,
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

        $organizationUser = OrganizationUser::where('username', $request->username)
            ->where('status', true) // Only active users
            ->first();
        
        if (!$organizationUser || !Hash::check($request->password, $organizationUser->password)) {
            return response()->json([
                'message' => 'رمز عبور اشتباه است.'
            ], 401);
        }
        
        // Generate a new token for the user
        $token = $organizationUser->createToken('organization-token')->accessToken;
        
        return response()->json([
            'message' => 'قفل صفحه باز شد.',
            'token' => $token,
            'user' => $organizationUser
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
