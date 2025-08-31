<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\BonusRuleController;
use App\Http\Controllers\Admin\NetworkController;
use App\Http\Controllers\Admin\EarningController;
use App\Http\Controllers\Admin\WithdrawalController;
use App\Http\Controllers\Admin\GenealogyController;

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
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'update'])->name('onboarding.update');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Referrals and Network
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
    Route::get('/network', [ReferralController::class, 'network'])->name('network.index');
    Route::get('/ajax/network', [ReferralController::class, 'ajaxNetwork'])->name('ajax.network');

    // Earnings
    Route::get('/earnings', [EarningsController::class, 'index'])->name('earnings.index');
    Route::get('/ajax/earnings/stats', [EarningsController::class, 'ajaxStats'])->name('ajax.earnings.stats');
    Route::get('/ajax/earnings/recent', [EarningsController::class, 'ajaxRecent'])->name('ajax.earnings.recent');

    // Withdrawals
    Route::get('/withdrawals', [WithdrawalsController::class, 'index'])->name('withdrawals.index');
    Route::get('/withdrawals/create', [WithdrawalsController::class, 'create'])->name('withdrawals.create');
    Route::post('/withdrawals', [WithdrawalsController::class, 'store'])->name('withdrawals.store');
    Route::get('/ajax/withdrawals/stats', [WithdrawalsController::class, 'ajaxStats'])->name('ajax.withdrawals.stats');
    Route::get('/ajax/withdrawals/recent', [WithdrawalsController::class, 'ajaxRecent'])->name('ajax.withdrawals.recent');

    // Packages
    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/{package}', [PackageController::class, 'show'])->name('packages.show');
    Route::post('/packages/{package}/purchase', [PackageController::class, 'purchase'])->name('packages.purchase');

    // Legacy routes (keeping for compatibility)
    Route::get('/dashboard/referrals', [ReferralController::class, 'index'])->name('dashboard.referrals');

    Route::get('/dashboard/payout', function () {
        return view('DashboardPayout');
    })->name('dashboard.payout');

    Route::get('/dashboard/profile', function () {
        return view('DashboardProfile');
    })->name('dashboard.profile');

    Route::get('/dashboard/network', function () {
        return view('DashboardNetwork');
    })->name('dashboard.network');

    Route::get('/dashboard/notification', function () {
        return view('DashboardNotification');
    })->name('dashboard.notification');

    Route::get('/dashboard/package', function () {
        return view('Dashboardpackage');
    })->name('dashboard.package');
});

// Admin dashboard Routes
Route::middleware(['auth', 'is_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [UserController::class, 'dashboard'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
        Route::post('/users/{user}/deny', [UserController::class, 'deny'])->name('users.deny');
        Route::resource('packages', PackageController::class);
        Route::resource('bonus_rules', BonusRuleController::class);
        Route::post('/bonus_rules/{rule}/activate', [BonusRuleController::class, 'activate'])->name('bonus_rules.activate');
        Route::post('/bonus_rules/{rule}/deactivate', [BonusRuleController::class, 'deactivate'])->name('bonus_rules.deactivate');
        Route::get('/network', [NetworkController::class, 'index'])->name('network.index');
        Route::get('/earnings', [EarningController::class, 'index'])->name('earnings.index');
        Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/deny', [WithdrawalController::class, 'deny'])->name('withdrawals.deny');
        Route::get('/genealogy', [GenealogyController::class, 'index'])->name('genealogy.index');
        Route::get('/genealogy/search', [GenealogyController::class, 'search'])->name('genealogy.search');
        Route::get('/genealogy/network/{userId}', [GenealogyController::class, 'network'])->name('genealogy.network');
    });




Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});




require __DIR__.'/auth.php';
