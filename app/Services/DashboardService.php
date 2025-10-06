<?php

namespace App\Services;

use App\Models\User;
use App\Models\Earning;
use App\Models\Withdrawal;
use App\Models\ReferralCode;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    /**
     * Get dashboard data for a user.
     */
    public function getDashboardData(User $user): array
    {
        return [
            'downlinesCount' => User::where('sponsor_id', $user->id)->count(),
            'totalEarnings' => Earning::where('user_id', $user->id)->sum('amount'),
            'pendingEarnings' => Earning::where('user_id', $user->id)->where('status', 'pending')->sum('amount'),
            'totalWithdrawals' => Withdrawal::where('user_id', $user->id)->sum('amount'),
            'pendingWithdrawals' => Withdrawal::where('user_id', $user->id)->where('status', 'pending')->count(),
            'accountBalance' => $user->account_balance ?? 0,
            'recentReferrals' => User::where('sponsor_id', $user->id)->latest()->take(5)->get(),
            'recentEarnings' => Earning::where('user_id', $user->id)->latest()->take(5)->get(),
            'networkStats' => $this->getNetworkStatistics($user),
            'earningsByType' => Earning::where('user_id', $user->id)->selectRaw('type, SUM(amount) as total')->groupBy('type')->get(),
            'referralCodes' => ReferralCode::where('assigned_to', $user->id)->get(),
        ];
    }

    /**
     * Get network statistics for a user.
     */
    public function getNetworkStatistics(User $user): array
    {
        $level1 = User::where('sponsor_id', $user->id)->count();
        $level2 = User::whereIn('sponsor_id', User::where('sponsor_id', $user->id)->pluck('id'))->count();
        $level3 = User::whereIn('sponsor_id',
            User::whereIn('sponsor_id', User::where('sponsor_id', $user->id)->pluck('id'))->pluck('id')
        )->count();

        return [
            'level1' => $level1,
            'level2' => $level2,
            'level3' => $level3,
            'total' => $level1 + $level2 + $level3
        ];
    }

    /**
     * Get earnings data for charts.
     */
    public function getEarningsChartData(User $user): array
    {
        $earningsData = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M');
            $labels[] = $monthName;

            $earnings = Earning::where('user_id', $user->id)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');

            $earningsData[] = (float) $earnings;
        }

        return [
            'labels' => $labels,
            'data' => $earningsData
        ];
    }

    /**
     * Get network growth data for charts.
     */
    public function getNetworkChartData(User $user): array
    {
        $networkData = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M');
            $labels[] = $monthName;

            $networkCount = User::where('sponsor_id', $user->id)
                ->where('created_at', '<=', $date)
                ->count();

            $networkData[] = $networkCount;
        }

        return [
            'labels' => $labels,
            'data' => $networkData
        ];
    }

    /**
     * Get earnings by type for charts.
     */
    public function getEarningsByTypeData(User $user): array
    {
        $earningsByType = Earning::where('user_id', $user->id)
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->get();

        return [
            'labels' => $earningsByType->pluck('type')->toArray(),
            'data' => $earningsByType->pluck('total')->toArray()
        ];
    }
}