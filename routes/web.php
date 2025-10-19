<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\PrintController;
use App\Http\Controllers\Admin\ViewController;
use App\Http\Controllers\Organization\ViewController as OrganizationViewController;
use App\Http\Controllers\Organization\AuthController as OrganizationAuthController;
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

// Organization Dashboard and Management Routes
Route::get('/', [OrganizationViewController::class, 'showDashboard'])->name('organization.dashboard');
Route::get('/profile', [OrganizationViewController::class, 'showProfile'])->name('organization.profile');
Route::get('/packages', [OrganizationViewController::class, 'showPackages'])->name('organization.packages.view');
Route::get('/users', [OrganizationViewController::class, 'showUsers'])->name('organization.users.view');

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
    
});
