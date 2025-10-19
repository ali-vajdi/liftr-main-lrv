<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\PrintController;
use App\Http\Controllers\Admin\ViewController;
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

Route::get('/', function () {
    return view('welcome');
});

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
    
});
