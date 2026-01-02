<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\PrintController;
use App\Http\Controllers\Admin\ViewController;
use App\Http\Controllers\Organization\ViewController as OrganizationViewController;
use App\Http\Controllers\Organization\AuthController as OrganizationAuthController;
use App\Http\Controllers\Public\BuildingController as PublicBuildingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Organization Panel Routes (Main Routes)
Route::get('/login', [OrganizationViewController::class, 'showLogin'])->name('organization.login');
Route::get('/lock-screen', [OrganizationViewController::class, 'showLockScreen'])->name('organization.lock');
Route::get('/forgot-password', [OrganizationAuthController::class, 'showForgotPassword'])->name('organization.forgot-password');
Route::get('/reset-password/{token}', [OrganizationAuthController::class, 'showResetPassword'])->name('organization.reset-password');
Route::get('/rp/{token}', [OrganizationAuthController::class, 'showResetPassword'])->name('organization.reset-password.short'); // Shorter route for SMS

// Organization Dashboard and Management Routes
Route::get('/', [OrganizationViewController::class, 'showDashboard'])->name('organization.dashboard');
Route::get('/profile', [OrganizationViewController::class, 'showProfile'])->name('organization.profile');
Route::get('/settings', [OrganizationViewController::class, 'showSettings'])->name('organization.settings');
Route::get('/packages', [OrganizationViewController::class, 'showPackages'])->name('organization.packages.view');
Route::get('/users', [OrganizationViewController::class, 'showUsers'])->name('organization.users.view');
Route::get('/technicians', [OrganizationViewController::class, 'showTechnicians'])->name('organization.technicians.view');
Route::get('/technicians/{technician}/dashboard', [OrganizationViewController::class, 'showTechnicianDashboard'])->name('organization.technicians.dashboard');
Route::get('/buildings', [OrganizationViewController::class, 'showBuildings'])->name('organization.buildings.view');
Route::get('/buildings/expiring', [OrganizationViewController::class, 'showExpiringBuildings'])->name('organization.buildings.expiring');
Route::get('/buildings/expired', [OrganizationViewController::class, 'showExpiredBuildings'])->name('organization.buildings.expired');
Route::get('/buildings/{building}/elevators', [OrganizationViewController::class, 'showBuildingElevators'])->name('organization.buildings.elevators.view');
Route::get('/buildings/{building}/dashboard', [OrganizationViewController::class, 'showBuildingDashboard'])->name('organization.buildings.dashboard');
Route::get('/buildings/{building}/contracts', [OrganizationViewController::class, 'showBuildingContracts'])->name('organization.buildings.contracts.view');
Route::get('/services/pending', [OrganizationViewController::class, 'showPendingServices'])->name('organization.services.pending');
Route::get('/services/assigned', [OrganizationViewController::class, 'showAssignedServices'])->name('organization.services.assigned');
Route::get('/services/completed', [OrganizationViewController::class, 'showCompletedServices'])->name('organization.services.completed');
Route::get('/services/all', [OrganizationViewController::class, 'showAllServices'])->name('organization.services.all');
Route::get('/packages/payment', [OrganizationViewController::class, 'showPayment'])->name('organization.payment');
Route::get('/packages/payment/page', [OrganizationViewController::class, 'showPayment'])->name('organization.payment.page');
Route::get('/payment/callback/{packageId}', [\App\Http\Controllers\Organization\PaymentController::class, 'paymentCallback'])->name('organization.payment.callback');
Route::get('/sms/payment/callback', [\App\Http\Controllers\Api\Organization\DashboardController::class, 'smsPaymentCallback'])->name('organization.sms.payment.callback');
Route::get('/messages', [OrganizationViewController::class, 'showMessages'])->name('organization.messages.view');
Route::get('/messages/sent', [OrganizationViewController::class, 'showSentMessages'])->name('organization.messages.sent');
Route::get('/transactions', [OrganizationViewController::class, 'showTransactions'])->name('organization.transactions.view');
Route::get('/buildings/{building}/financial-dashboard', [OrganizationViewController::class, 'showFinancialDashboard'])->name('organization.buildings.financial-dashboard.view');
Route::get('/financial/all-debts', [OrganizationViewController::class, 'showAllDebts'])->name('organization.financial.all-debts');
Route::get('/financial/invoices', [OrganizationViewController::class, 'showInvoices'])->name('organization.financial.invoices.index');
Route::get('/financial/invoices/create', [OrganizationViewController::class, 'showCreateInvoice'])->name('organization.financial.invoices.create');
Route::get('/financial/invoices/{invoice}/edit', [OrganizationViewController::class, 'showEditInvoice'])->name('organization.financial.invoices.edit');

// Public Routes for Buildings
Route::get('/buildings/{building}/services', [PublicBuildingController::class, 'showServices'])->name('public.buildings.services');
Route::get('/d/{slug}', [PublicBuildingController::class, 'showAssignedService'])->name('public.services.assigned.show');
Route::get('/buildings/{building}/services/{service}/print', [PublicBuildingController::class, 'printService'])->name('public.services.print');

// Organization PDF Download Route (token-based, no middleware needed)
Route::get('/organization/services/{service}/pdf/download', [\App\Http\Controllers\Api\Organization\ServiceController::class, 'downloadPdf'])->name('organization.services.pdf.download');

Route::prefix('admin')->name('admin.')->group(function () {
    // Auth Routes
    Route::get('login', [ViewController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('lock-screen', [ViewController::class, 'showLockScreen'])->name('lock');
    Route::post('unlock-screen', [AuthController::class, 'unlockScreen'])->name('unlock');

    // Dashboard Route
    Route::get('dashboard', [ViewController::class, 'showDashboard'])->name('dashboard');
    // Print Route
    Route::post('print/template', [PrintController::class, 'template'])->name('print.template');


    // Moderators Management Routes
    Route::get('moderators', [ViewController::class, 'showModerators'])->name('moderators.view');
    Route::get('profile', [ViewController::class, 'showProfile'])->name('profile');

    // Organizations Management Routes
    Route::get('organizations', [ViewController::class, 'showOrganizations'])->name('organizations.view');
    Route::get('organizations/{organization}/users', [ViewController::class, 'showOrganizationUsers'])->name('organizations.users.view');
    Route::get('organizations/{organization}/packages', [ViewController::class, 'showOrganizationPackages'])->name('organizations.packages.view');

    // Packages Management Routes
    Route::get('packages', [ViewController::class, 'showPackages'])->name('packages.view');
    
    // Unit Checklists Management Routes
    Route::get('unit-checklists', [ViewController::class, 'showUnitChecklists'])->name('unit-checklists.view');
    
    // Description Checklists Management Routes
    Route::get('description-checklists', [ViewController::class, 'showDescriptionChecklists'])->name('description-checklists.view');
    
    // SMS Management Routes
    Route::get('sms', [ViewController::class, 'showSms'])->name('sms.view');
    
    // Accounting/Transactions Management Routes
    Route::get('transactions', [ViewController::class, 'showTransactions'])->name('transactions.view');
    
    // Messages Management Routes
    Route::get('messages', [ViewController::class, 'showMessages'])->name('messages.view');
    
    // Application Versions Management Routes
    Route::get('application-versions', [ViewController::class, 'showApplicationVersions'])->name('application-versions.view');
    
});
