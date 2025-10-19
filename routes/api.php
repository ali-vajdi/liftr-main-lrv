<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ModeratorController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\OrganizationUserController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\OrganizationPackageController;
use App\Http\Controllers\Api\Organization\PackageController as OrgPackageController;
use App\Http\Controllers\Api\Organization\UserController as OrgUserController;
use App\Http\Controllers\Organization\AuthController as OrganizationAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('unlock-screen', [AuthController::class, 'unlockScreen']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('lock-screen', [AuthController::class, 'lockScreen']);
        Route::get('dashboard-data', [DashboardController::class, 'getDashboardData']);
        Route::get('check-auth', [AuthController::class, 'checkAuth']);

        // Moderators Management
        Route::apiResource('moderators', ModeratorController::class);
        Route::get('profile', [ModeratorController::class, 'profile']);
        Route::put('profile', [ModeratorController::class, 'updateProfile']);

        // Organizations Management
        Route::apiResource('organizations', OrganizationController::class);
        
        // Organization Users Management
        Route::get('organizations/{organization}/users', [OrganizationUserController::class, 'index']);
        Route::post('organizations/{organization}/users', [OrganizationUserController::class, 'store']);
        Route::get('organizations/{organization}/users/{user}', [OrganizationUserController::class, 'show']);
        Route::put('organizations/{organization}/users/{user}', [OrganizationUserController::class, 'update']);
        Route::delete('organizations/{organization}/users/{user}', [OrganizationUserController::class, 'destroy']);
        Route::post('organizations/{organization}/users/{user}/credentials', [OrganizationUserController::class, 'setCredentials']);

        // Packages Management
        Route::apiResource('packages', PackageController::class);
        
        // Organization Packages Management
        Route::get('organizations/{organization}/packages', [OrganizationPackageController::class, 'index']);
        Route::post('organizations/{organization}/packages', [OrganizationPackageController::class, 'store']);
        Route::get('organizations/{organization}/packages/available', [OrganizationPackageController::class, 'getAvailablePackages']);
        Route::get('organizations/{organization}/packages/current', [OrganizationPackageController::class, 'getOrganizationCurrentPackage']);
        Route::get('organizations/{organization}/packages/{package}', [OrganizationPackageController::class, 'show']);
        Route::put('organizations/{organization}/packages/{package}', [OrganizationPackageController::class, 'update']);
        Route::delete('organizations/{organization}/packages/{package}', [OrganizationPackageController::class, 'destroy']);
    });
});

// Organization Panel API Routes
Route::prefix('organization')->name('organization.')->group(function () {
    // Organization Authentication Routes
    Route::post('login', [OrganizationAuthController::class, 'login']);
    Route::post('unlock-screen', [OrganizationAuthController::class, 'unlockScreen']);
    
    // Protected Organization API Routes
    Route::middleware('auth:organization_api')->group(function () {
        Route::post('logout', [OrganizationAuthController::class, 'logout']);
        Route::post('lock-screen', [OrganizationAuthController::class, 'lockScreen']);
        Route::get('check-auth', [OrganizationAuthController::class, 'checkAuth']);
        
        // Organization Packages API
        Route::get('packages', [OrgPackageController::class, 'index']);
        Route::get('packages/{package}', [OrgPackageController::class, 'show']);
        
        // Organization Users API
        Route::get('users', [OrgUserController::class, 'index']);
        Route::get('users/{user}', [OrgUserController::class, 'show']);
    });
});
