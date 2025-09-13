<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\EarningsController;
use App\Http\Controllers\WithdrawalsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BonusRuleController;
use App\Http\Controllers\Admin\BonusSettingsController;
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

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


// for user dashboard Routes
Route::middleware(['auth', 'verified', 'profile.complete'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'update'])->name('onboarding.update');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/ajax/dashboard/charts', [DashboardController::class, 'ajaxChartData'])->name('ajax.dashboard.charts');
    Route::get('/ajax/dashboard/earnings-by-type', [DashboardController::class, 'ajaxEarningsByType'])->name('ajax.dashboard.earnings-by-type');

    // Referrals and Network
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');

    // Earnings
    Route::get('/earnings', [EarningsController::class, 'index'])->name('earnings.index');
    Route::get('/ajax/earnings/stats', [EarningsController::class, 'ajaxStats'])->name('ajax.earnings.stats');
    Route::get('/ajax/earnings/recent', [EarningsController::class, 'ajaxRecent'])->name('ajax.earnings.recent');

    // Withdrawals (Payout functionality)
    Route::post('/withdrawals', [WithdrawalsController::class, 'store'])->name('withdrawals.store');
    Route::get('/ajax/withdrawals/stats', [WithdrawalsController::class, 'ajaxStats'])->name('ajax.withdrawals.stats');
    Route::get('/ajax/withdrawals/recent', [WithdrawalsController::class, 'ajaxRecent'])->name('ajax.withdrawals.recent');

    // Legacy routes (keeping for compatibility)
    Route::get('/dashboard/referrals', [ReferralController::class, 'index'])->name('dashboard.referrals');

    Route::get('/dashboard/payout', [WithdrawalsController::class, 'dashboard'])->name('dashboard.payout');

    Route::get('/dashboard/profile', function () {
        return view('DashboardProfile');
    })->name('dashboard.profile');

    Route::get('/dashboard/network', [DashboardController::class, 'network'])->name('dashboard.network');

    Route::get('/dashboard/notification', [DashboardController::class, 'notification'])->name('dashboard.notification');
});

// Notification Routes
Route::middleware(['auth', 'verified', 'profile.complete'])->group(function () {
    Route::post('/notifications/{notification}/read', function (\App\Models\Notification $notification) {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }
        $notification->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.read');

    Route::delete('/notifications/{notification}', function (\App\Models\Notification $notification) {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }
        $notificationService = new \App\Services\NotificationService();
        $notificationService->deleteNotification($notification->id, auth()->id());
        return response()->json(['success' => true]);
    })->name('notifications.delete');

    Route::post('/notifications/mark-all-read', function () {
        $notificationService = new \App\Services\NotificationService();
        $notificationService->markAllAsRead(auth()->id());
        return response()->json(['success' => true]);
    })->name('notifications.mark-all-read');

    Route::post('/notifications/delete-all-read', function () {
        $notificationService = new \App\Services\NotificationService();
        $notificationService->deleteAllRead(auth()->id());
        return response()->json(['success' => true]);
    })->name('notifications.delete-all-read');
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
        Route::post('/users/{user}/generate-referral-code', [UserController::class, 'generateReferralCode'])->name('users.generate-referral-code');
        Route::resource('bonus_rules', BonusRuleController::class);
        Route::post('/bonus_rules/{rule}/activate', [BonusRuleController::class, 'activate'])->name('bonus_rules.activate');
        Route::post('/bonus_rules/{rule}/deactivate', [BonusRuleController::class, 'deactivate'])->name('bonus_rules.deactivate');
        Route::get('/bonus-settings', [BonusSettingsController::class, 'index'])->name('bonus_settings.index');
        Route::put('/bonus-settings', [BonusSettingsController::class, 'update'])->name('bonus_settings.update');
        Route::resource('referral_codes', ReferralCodeController::class);
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
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});




require __DIR__.'/auth.php';
