<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Earning;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Check if user has completed onboarding (all required fields)
        if (!$user->phone || !$user->address || !$user->city || !$user->province || !$user->shipping_name) {
            return redirect()->route('onboarding');
        }

        // Get user's downlines count
        $downlinesCount = User::where('sponsor_id', $user->id)->count();

        // Get user's earnings
        $totalEarnings = Earning::where('user_id', $user->id)->sum('amount');
        $pendingEarnings = Earning::where('user_id', $user->id)->where('status', 'pending')->sum('amount');

        // Get user's withdrawals
        $totalWithdrawals = Withdrawal::where('user_id', $user->id)->sum('amount');
        $pendingWithdrawals = Withdrawal::where('user_id', $user->id)->where('status', 'pending')->count();

        // Get user's account balance
        $accountBalance = $user->account_balance ?? 0;

        // Get recent referrals
        $recentReferrals = User::where('sponsor_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // Get recent earnings
        $recentEarnings = Earning::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // Get network statistics
        $networkStats = $this->getNetworkStatistics($user);

        // Get earnings by type
        $earningsByType = Earning::where('user_id', $user->id)
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->get();

        return view('dashboard', compact(
            'user',
            'downlinesCount',
            'totalEarnings',
            'pendingEarnings',
            'totalWithdrawals',
            'pendingWithdrawals',
            'accountBalance',
            'recentReferrals',
            'recentEarnings',
            'networkStats',
            'earningsByType'
        ));
    }

    private function getNetworkStatistics($user)
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
}