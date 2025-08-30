<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\BonusRuleController;
use App\Http\Controllers\Admin\NetworkController;
use App\Http\Controllers\Admin\EarningController;
use App\Http\Controllers\Admin\WithdrawalController;

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
    return view('home');
});


// for user dashboard Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/dashboard/referrals', function () {
        return view('dashboardreferrals');
    })->name('dashboard.referrals');


        Route::get('/dashboard/payout', function () {
        return view('dashboardpayout');
    })->name('dashboard.payout');
    
    Route::get('/dashboard/profile', function () {
        return view('dashboardprofile');
    })->name('dashboard.profile');


       Route::get('/dashboard/network', function () {
        return view('dashboardnetwork');
    })->name('dashboard.network');

          Route::get('/dashboard/notification', function () {
        return view('dashboardnotification');
    })->name('dashboard.notification');


    Route::get('/dashboard/package', function () {
        return view('dashboardpackage');
    })->name('dashboard.package');


});

// Admin dashboard Routes
Route::middleware(['auth', 'is_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // Dashboard
    Route::get('/', [UserController::class, 'dashboard'])->name('dashboard');

    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/deny', [UserController::class, 'deny'])->name('users.deny');

    // Packages
    Route::resource('packages', PackageController::class);

    // Bonus Rules
    Route::resource('bonus-rules', BonusRuleController::class);
    Route::post('/bonus-rules/{rule}/activate', [BonusRuleController::class, 'activate'])->name('bonus-rules.activate');
    Route::post('/bonus-rules/{rule}/deactivate', [BonusRuleController::class, 'deactivate'])->name('bonus-rules.deactivate');

    // Network Viewer
    Route::get('/network', [NetworkController::class, 'index'])->name('network.index');

    // Earnings Reports
    Route::get('/earnings', [EarningController::class, 'index'])->name('earnings.index');

    // Withdrawals
    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/withdrawals/{withdrawal}/deny', [WithdrawalController::class, 'deny'])->name('withdrawals.deny');
});










Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});




require __DIR__.'/auth.php';
