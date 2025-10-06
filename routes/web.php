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
use App\Http\Controllers\Admin\AdminCodeController;
use App\Http\Controllers\Admin\ReferralCodeController;
use App\Http\Controllers\GenealogyController as UserGenealogyController;

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
    Route::get('/dashboard/network', [DashboardController::class, 'network'])->name('dashboard.network');
    Route::get('/network', [DashboardController::class, 'network'])->name('network');
    Route::get('/dashboard/payout', [WithdrawalsController::class, 'dashboard'])->name('dashboard.payout');
    Route::get('/notifications', [DashboardController::class, 'notification'])->name('notifications');
    Route::get('/ajax/dashboard/charts', [DashboardController::class, 'ajaxChartData'])->name('ajax.dashboard.charts');
    Route::get('/ajax/dashboard/earnings-by-type', [DashboardController::class, 'ajaxEarningsByType'])->name('ajax.dashboard.earnings-by-type');
    Route::get('/ajax/dashboard/network-stats', [DashboardController::class, 'ajaxNetworkStats'])->name('ajax.dashboard.network_stats');
    Route::get('/ajax/dashboard/balance-stats', [DashboardController::class, 'ajaxBalanceStats'])->name('ajax.dashboard.balance_stats');
    Route::get('/ajax/dashboard/network-data', [DashboardController::class, 'ajaxNetworkData'])->name('ajax.dashboard.network_data');
    Route::get('/ajax/dashboard/sales-data', [DashboardController::class, 'ajaxSalesData'])->name('ajax.dashboard.sales_data');
    Route::get('/ajax/dashboard/earnings-breakdown', [DashboardController::class, 'ajaxEarningsBreakdown'])->name('ajax.dashboard.earnings_breakdown');
    Route::get('/ajax/dashboard/balance-data', [DashboardController::class, 'ajaxBalanceData'])->name('ajax.dashboard.balance_data');

    // Referrals and Network
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
    Route::get('/genealogy/{user}', [UserGenealogyController::class, 'show'])->name('genealogy.show');
    Route::get('/genealogy/{user}/network-data', [UserGenealogyController::class, 'networkData'])->name('genealogy.network-data');
    Route::get('/genealogy/{user}/stats', [UserGenealogyController::class, 'userStats'])->name('genealogy.user-stats');
    Route::get('/genealogy/{user}/export', [UserGenealogyController::class, 'export'])->name('genealogy.export');

    // Earnings
    Route::get('/earnings', [EarningsController::class, 'index'])->name('earnings.index');
    Route::get('/ajax/earnings/stats', [EarningsController::class, 'ajaxStats'])->name('ajax.earnings.stats');
    Route::get('/ajax/earnings/recent', [EarningsController::class, 'ajaxRecent'])->name('ajax.earnings.recent');

    // Withdrawals (Payout functionality)
    Route::post('/withdrawals', [WithdrawalsController::class, 'store'])->name('withdrawals.store');
    Route::get('/ajax/withdrawals/stats', [WithdrawalsController::class, 'ajaxStats'])->name('withdrawals.ajax.stats');
    Route::get('/ajax/withdrawals/recent', [WithdrawalsController::class, 'ajaxRecent'])->name('withdrawals.ajax.recent');

});

// Notification Routes
Route::middleware(['auth', 'verified', 'profile.complete'])->group(function () {
    Route::post('/notifications/{notification}/read', function (\App\Models\Notification $notification) {
        try {
            if ($notification->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to notification'
                ], 403);
            }

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking notification as read: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    })->name('notifications.read');

    Route::delete('/notifications/{notification}', function (\App\Models\Notification $notification) {
        try {
            if ($notification->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to notification'
                ], 403);
            }

            $notificationService = new \App\Services\NotificationService();
            $notificationService->deleteNotification($notification->id, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting notification: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification'
            ], 500);
        }
    })->name('notifications.delete');

    Route::post('/notifications/mark-all-read', function () {
        try {
            $notificationService = new \App\Services\NotificationService();
            $notificationService->markAllAsRead(auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking all notifications as read: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read'
            ], 500);
        }
    })->name('notifications.mark-all-read');

    Route::post('/notifications/delete-all-read', function () {
        try {
            $notificationService = new \App\Services\NotificationService();
            $notificationService->deleteAllRead(auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'All read notifications deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting all read notifications: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete read notifications'
            ], 500);
        }
    })->name('notifications.delete-all-read');

    // Real-time notification routes
    Route::get('/ajax/notifications/check-updates', function () {
        try {
            $user = auth()->user();
            $total = \App\Models\Notification::where('user_id', $user->id)->count();
            $unread = \App\Models\Notification::where('user_id', $user->id)->where('is_read', false)->count();

            return response()->json([
                'success' => true,
                'total' => $total,
                'unread' => $unread
            ]);
        } catch (\Exception $e) {
            \Log::error('Error checking notification updates: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to check notification updates'
            ], 500);
        }
    })->name('ajax.notifications.check_updates');

    Route::get('/ajax/notifications/dropdown', function () {
        try {
            $user = auth()->user();
            $notifications = \App\Models\Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            $unreadCount = \App\Models\Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading notification dropdown: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load notifications'
            ], 500);
        }
    })->name('ajax.notifications.dropdown');

    Route::get('/ajax/notifications/list', function (Request $request) {
        $user = auth()->user();
        $type = $request->get('type', 'all');

        $query = \App\Models\Notification::where('user_id', $user->id);

        if ($type !== 'all') {
            if ($type === 'unread') {
                $query->where('is_read', false);
            } else {
                $query->where('type', $type);
            }
        }

        $notifications = $query->latest()->take(20)->get();

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    })->name('ajax.notifications.list');
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
        Route::resource('admin_codes', AdminCodeController::class);
        Route::get('/admin_codes/create', [AdminCodeController::class, 'create'])->name('admin_codes.create');
        Route::post('/admin_codes/generate', [AdminCodeController::class, 'generate'])->name('admin_codes.generate');
        Route::post('/admin_codes/{code}/assign', [AdminCodeController::class, 'assign'])->name('admin_codes.assign');
        Route::post('/admin_codes/{code}/issue', [AdminCodeController::class, 'issue'])->name('admin_codes.issue');
        Route::post('/admin_codes/{code}/revoke', [AdminCodeController::class, 'revoke'])->name('admin_codes.revoke');
        Route::get('/admin_codes/download', [AdminCodeController::class, 'download'])->name('admin_codes.download');
        Route::get('/admin_codes/batches', [AdminCodeController::class, 'batches'])->name('admin_codes.batches');
        Route::resource('referral_codes', ReferralCodeController::class);
        Route::post('/referral_codes/generate', [ReferralCodeController::class, 'generate'])->name('referral_codes.generate');
        Route::post('/referral_codes/{referral_code}/assign', [ReferralCodeController::class, 'assign'])->name('referral_codes.assign');
        Route::get('/referral_codes/search/users', [ReferralCodeController::class, 'searchUsers'])->name('referral_codes.search_users');
        Route::get('/referral_codes/statistics', [ReferralCodeController::class, 'getStatistics'])->name('referral_codes.statistics');
        Route::post('/referral_codes/bulk-export', [ReferralCodeController::class, 'bulkExport'])->name('referral_codes.bulk_export');
        Route::post('/referral_codes/bulk-assign', [ReferralCodeController::class, 'bulkAssign'])->name('referral_codes.bulk_assign');
        Route::post('/referral_codes/bulk-delete', [ReferralCodeController::class, 'bulkDelete'])->name('referral_codes.bulk_delete');
        Route::get('/network', [NetworkController::class, 'index'])->name('network.index');
        Route::get('/earnings', [EarningController::class, 'index'])->name('earnings.index');
        Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/deny', [WithdrawalController::class, 'deny'])->name('withdrawals.deny');
        Route::get('/genealogy', [GenealogyController::class, 'index'])->name('genealogy.index');
        Route::get('/genealogy/search', [GenealogyController::class, 'search'])->name('genealogy.search');
        Route::get('/genealogy/ajax-search', [GenealogyController::class, 'ajaxSearch'])->name('genealogy.ajax_search');
        Route::get('/genealogy/network/{userId}', [GenealogyController::class, 'network'])->name('genealogy.network');
    });




Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});




require __DIR__.'/auth.php';
