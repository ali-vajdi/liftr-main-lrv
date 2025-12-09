<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ModeratorController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\OrganizationUserController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\OrganizationPackageController;
use App\Http\Controllers\Api\UnitChecklistController;
use App\Http\Controllers\Api\DescriptionChecklistController;
use App\Http\Controllers\Api\SmsController;
use App\Http\Controllers\Api\Organization\PackageController as OrgPackageController;
use App\Http\Controllers\Api\Organization\UserController as OrgUserController;
use App\Http\Controllers\Api\Organization\TechnicianController as OrgTechnicianController;
use App\Http\Controllers\Api\Organization\BuildingController as OrgBuildingController;
use App\Http\Controllers\Api\Organization\ElevatorController as OrgElevatorController;
use App\Http\Controllers\Api\Organization\ServiceController as OrgServiceController;
use App\Http\Controllers\Api\Organization\DashboardController as OrgDashboardController;
use App\Http\Controllers\Organization\AuthController as OrganizationAuthController;
use App\Http\Controllers\Api\TechnicianAuthController;
use App\Http\Controllers\Api\Technician\ServiceController as TechnicianServiceController;
use App\Http\Controllers\Api\Technician\ReportController as TechnicianReportController;
use App\Http\Controllers\Api\Technician\VersionController as TechnicianVersionController;
use App\Http\Controllers\Api\Admin\ApplicationVersionController;
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
        
        // Unit Checklists Management
        Route::apiResource('unit-checklists', UnitChecklistController::class);
        
        // Description Checklists Management
        Route::apiResource('description-checklists', DescriptionChecklistController::class);
        
        // SMS Management
        Route::apiResource('sms', SmsController::class);
        
        // Organization Packages Management
        Route::get('organizations/{organization}/packages', [OrganizationPackageController::class, 'index']);
        Route::post('organizations/{organization}/packages', [OrganizationPackageController::class, 'store']);
        Route::get('organizations/{organization}/packages/available', [OrganizationPackageController::class, 'getAvailablePackages']);
        Route::get('organizations/{organization}/packages/current', [OrganizationPackageController::class, 'getOrganizationCurrentPackage']);
        Route::get('organizations/{organization}/packages/{package}', [OrganizationPackageController::class, 'show']);
        Route::put('organizations/{organization}/packages/{package}', [OrganizationPackageController::class, 'update']);
        Route::delete('organizations/{organization}/packages/{package}', [OrganizationPackageController::class, 'destroy']);
        
        // Package Payments Management
        Route::post('organizations/{organization}/packages/{package}/payments', [OrganizationPackageController::class, 'addPayment']);
        Route::get('organizations/{organization}/packages/{package}/payments', [OrganizationPackageController::class, 'getPayments']);
        Route::delete('organizations/{organization}/packages/{package}/payments/{payment}', [OrganizationPackageController::class, 'deletePayment']);
        Route::post('organizations/{organization}/packages/{package}/force-disable', [OrganizationPackageController::class, 'forceDisable']);
        
        // Payment Methods
        Route::get('payment-methods', [OrganizationPackageController::class, 'getPaymentMethods']);
        
        // Transactions/Accounting Management
        Route::get('transactions', [\App\Http\Controllers\Api\TransactionController::class, 'index']);
        Route::get('transactions/{transaction}', [\App\Http\Controllers\Api\TransactionController::class, 'show']);
        
        // Messages Management (Admin to Organizations)
        Route::apiResource('messages', \App\Http\Controllers\Api\Admin\MessageController::class);
        
        // Application Versions Management
        Route::apiResource('application-versions', ApplicationVersionController::class);
    });
});

// Organization Panel API Routes
Route::prefix('organization')->name('organization.')->group(function () {
    // Organization Authentication Routes
    Route::post('login', [OrganizationAuthController::class, 'login']);
    Route::post('unlock-screen', [OrganizationAuthController::class, 'unlockScreen']);
    Route::post('forgot-password', [OrganizationAuthController::class, 'forgotPassword']);
    Route::post('reset-password', [OrganizationAuthController::class, 'resetPassword']);
    
    // Protected Organization API Routes
    Route::middleware('auth:organization_api')->group(function () {
        Route::post('logout', [OrganizationAuthController::class, 'logout']);
        Route::post('lock-screen', [OrganizationAuthController::class, 'lockScreen']);
        Route::get('check-auth', [OrganizationAuthController::class, 'checkAuth']);
        Route::get('profile', [OrganizationAuthController::class, 'profile']);
        Route::put('profile', [OrganizationAuthController::class, 'updateProfile']);
        Route::post('organization', [OrganizationAuthController::class, 'updateOrganization']);
        
        // Payment routes (excluded from payment check)
        Route::get('payment/info', [\App\Http\Controllers\Organization\PaymentController::class, 'getPaymentInfo']);
        Route::post('payment/process', [\App\Http\Controllers\Organization\PaymentController::class, 'processPayment']);
        Route::post('payment/activate-package', [\App\Http\Controllers\Organization\PaymentController::class, 'activatePackage']);
        Route::get('payment-methods', [\App\Http\Controllers\Api\OrganizationPackageController::class, 'getPaymentMethods']);
        
        // All other routes require payment check
        Route::middleware('check.package.payment')->group(function () {
            Route::get('dashboard-data', [OrgDashboardController::class, 'getDashboardData']);
        
        // Organization Packages API
        Route::get('packages', [OrgPackageController::class, 'index']);
        Route::get('packages/{package}', [OrgPackageController::class, 'show']);
        
        // Organization Users API
        Route::get('users', [OrgUserController::class, 'index']);
        Route::post('users', [OrgUserController::class, 'store']);
        Route::get('users/{user}', [OrgUserController::class, 'show']);
        Route::put('users/{user}', [OrgUserController::class, 'update']);
        Route::delete('users/{user}', [OrgUserController::class, 'destroy']);
        
        // Organization Technicians API
        Route::apiResource('technicians', OrgTechnicianController::class);
        Route::post('technicians/{technician}/credentials', [OrgTechnicianController::class, 'setCredentials']);
        Route::get('technicians/{technician}/dashboard', [OrgTechnicianController::class, 'dashboard']);
        
        // Organization Buildings API
        Route::apiResource('buildings', OrgBuildingController::class);
        Route::get('buildings/{building}/dashboard', [OrgBuildingController::class, 'dashboard']);
        Route::get('provinces', [OrgBuildingController::class, 'getProvinces']);
        Route::get('cities-by-province', [OrgBuildingController::class, 'getCitiesByProvince']);
        
        // Organization Elevators API
        Route::apiResource('buildings.elevators', OrgElevatorController::class);
        Route::post('buildings/{buildingId}/elevators/bulk', [OrgElevatorController::class, 'bulk']);
        
        // Organization Services API
        Route::get('services/pending', [OrgServiceController::class, 'pending']);
        Route::get('services/assigned', [OrgServiceController::class, 'assigned']);
        Route::get('services/completed', [OrgServiceController::class, 'completed']);
        Route::get('services/all', [OrgServiceController::class, 'all']);
        Route::post('services', [OrgServiceController::class, 'store']);
        Route::post('services/{service}/assign-technician', [OrgServiceController::class, 'assignTechnician']);
        Route::post('services/{service}/change-technician', [OrgServiceController::class, 'changeTechnician']);
        Route::post('services/{service}/update-visit', [OrgServiceController::class, 'updateVisit']);
        Route::post('services/{service}/cancel', [OrgServiceController::class, 'cancelService']);
        Route::post('services/{service}/revert', [OrgServiceController::class, 'revertService']);
        Route::post('services/{service}/cancel-building', [OrgServiceController::class, 'cancelBuildingAndService']);
        Route::get('services/{service}/building-info', [OrgServiceController::class, 'getBuildingInfo']);
        Route::post('services/{service}/resend-checklist-sms', [OrgServiceController::class, 'resendChecklistSms']);
        Route::get('services/technicians', [OrgServiceController::class, 'getTechnicians']);
        
        // Organization SMS API
        Route::get('sms/statistics', [\App\Http\Controllers\Api\Organization\SmsController::class, 'statistics']);
        Route::post('sms/increase-balance', [\App\Http\Controllers\Api\Organization\DashboardController::class, 'increaseSmsBalance']);
        Route::get('sms/patterns', [\App\Http\Controllers\Api\Organization\SmsController::class, 'getPatterns']);
        Route::get('sms/patterns/{code}', [\App\Http\Controllers\Api\Organization\SmsController::class, 'getPattern']);
        
        // Messages Management (Organization)
        Route::get('messages/unread-count', [\App\Http\Controllers\Api\Organization\MessageController::class, 'unreadCount']);
        Route::get('messages', [\App\Http\Controllers\Api\Organization\MessageController::class, 'index']);
        Route::get('messages/sent', [\App\Http\Controllers\Api\Organization\MessageController::class, 'sent']);
        Route::get('messages/sent/{message}', [\App\Http\Controllers\Api\Organization\MessageController::class, 'showSent']);
        Route::post('messages', [\App\Http\Controllers\Api\Organization\MessageController::class, 'store']);
        Route::get('messages/{message}', [\App\Http\Controllers\Api\Organization\MessageController::class, 'show']);
        Route::post('messages/{message}/mark-read', [\App\Http\Controllers\Api\Organization\MessageController::class, 'markAsRead']);
        
        // Transactions Management (Organization)
        Route::get('transactions', [\App\Http\Controllers\Api\Organization\TransactionController::class, 'index']);
        Route::get('transactions/{transaction}', [\App\Http\Controllers\Api\Organization\TransactionController::class, 'show']);
        });
    });
});

// Technician Panel API Routes
Route::prefix('technician')->name('technician.')->group(function () {
    // Technician Authentication Routes
    Route::post('login', [TechnicianAuthController::class, 'login']);
    Route::post('send-otp', [TechnicianAuthController::class, 'sendOtp']);
    Route::post('verify-otp', [TechnicianAuthController::class, 'verifyOtp']);
    
    // Public Version Check API (no authentication required)
    Route::post('check-update', [TechnicianVersionController::class, 'checkUpdate']);
    
    // Protected Technician API Routes
    Route::middleware('auth:technician_api')->group(function () {
        Route::post('logout', [TechnicianAuthController::class, 'logout']);
        Route::get('check-auth', [TechnicianAuthController::class, 'checkAuth']);
        Route::get('profile', [TechnicianAuthController::class, 'profile']);
        
        // Technician Messages API
        Route::get('messages', [\App\Http\Controllers\Api\Technician\MessageController::class, 'index']);
        Route::get('messages/unread-count', [\App\Http\Controllers\Api\Technician\MessageController::class, 'unreadCount']);
        Route::post('messages/{message}/mark-read', [\App\Http\Controllers\Api\Technician\MessageController::class, 'markAsRead']);
        Route::post('messages/mark-all-read', [\App\Http\Controllers\Api\Technician\MessageController::class, 'markAllAsRead']);
        
        // Technician Services API
        Route::get('services/assigned-buildings', [TechnicianServiceController::class, 'assignedBuildings']);
        Route::get('services/{service}', [TechnicianServiceController::class, 'show']);
        Route::post('services/{service}/submit-checklist', [TechnicianServiceController::class, 'submitChecklist']);
        
        // Technician Reports API
        Route::get('reports', [TechnicianReportController::class, 'index']);
    });
});

// Public API Routes (No authentication required)
Route::prefix('public')->name('public.')->group(function () {
    Route::post('services/{service}/user-note', [\App\Http\Controllers\Api\Public\ServiceController::class, 'updateUserNote']);
    Route::post('services/{service}/pdf/send-code', [\App\Http\Controllers\Api\Public\ServiceController::class, 'sendPdfVerificationCode']);
    Route::post('services/{service}/pdf/verify-code', [\App\Http\Controllers\Api\Public\ServiceController::class, 'verifyPdfCode']);
});
