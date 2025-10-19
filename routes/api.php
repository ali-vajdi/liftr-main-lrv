<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ModeratorController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\OrganizationUserController;
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
    });
});
