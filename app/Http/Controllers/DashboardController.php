<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Earning;
use App\Models\Withdrawal;
use App\Models\ReferralCode;
use App\Models\BinaryTree;
use Illuminate\Support\Facades\Auth;
use App\Services\BinaryTreeService;

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

        // Get binary tree data
        $binaryTreeService = new BinaryTreeService();
        $binaryTreeData = $binaryTreeService->getTreeData($user);

        // Get user's referral codes
        $referralCodes = ReferralCode::where('assigned_to', $user->id)->get();

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
            'earningsByType',
            'binaryTreeData',
            'referralCodes'
        ));
    }

    public function network()
    {
        $user = Auth::user();

        // Build network tree
        $networkTree = $this->buildNetworkTree($user);

        return view('DashboardNetwork', compact('networkTree'));
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

    private function buildNetworkTree($user, $depth = 0, $maxDepth = 4)
    {
        if ($depth >= $maxDepth) {
            return null;
        }

        $children = User::where('sponsor_id', $user->id)
            ->with('earnings')
            ->get()
            ->map(function($child) use ($depth, $maxDepth) {
                $childData = $this->buildNetworkTree($child, $depth + 1, $maxDepth);
                if ($childData) {
                    $childData['placement_side'] = $child->placement_side;
                }
                return $childData;
            })
            ->filter()
            ->values();

        $totalEarnings = $user->earnings->sum('amount');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'level' => $depth + 1,
            'earnings' => $totalEarnings,
            'children' => $children,
            'placement_side' => $user->placement_side,
            'created_at' => $user->created_at->format('M d, Y')
        ];
    }

    public function ajaxChartData()
    {
        $user = Auth::user();

        // Get earnings data for the last 12 months
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

        // Get network growth data
        $networkData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $networkCount = User::where('sponsor_id', $user->id)
                ->where('created_at', '<=', $date)
                ->count();

            $networkData[] = $networkCount;
        }

        return response()->json([
            'success' => true,
            'earnings' => [
                'labels' => $labels,
                'data' => $earningsData
            ],
            'network' => [
                'labels' => $labels,
                'data' => $networkData
            ]
        ]);
    }

    public function ajaxEarningsByType()
    {
        $user = Auth::user();

        $earningsByType = Earning::where('user_id', $user->id)
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->get();

        $labels = $earningsByType->pluck('type')->toArray();
        $data = $earningsByType->pluck('total')->toArray();

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'data' => $data
        ]);
    }
}