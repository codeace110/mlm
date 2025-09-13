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
use App\Services\NotificationService;

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

        // Calculate growth percentages (simplified - in real app, compare with previous period)
        $downlineGrowthPercent = '+0%'; // Placeholder
        $balanceGrowthPercent = '+0%'; // Placeholder
        $withdrawalGrowthPercent = '+0%'; // Placeholder
        $pendingEarningsGrowthPercent = '+0%'; // Placeholder
        $salesText = 'Sales data will be displayed here'; // Placeholder
        $salesGrowthPercent = '0%'; // Placeholder
        $salesGrowthPeriod = 'this period'; // Placeholder

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
            'referralCodes',
            'downlineGrowthPercent',
            'balanceGrowthPercent',
            'withdrawalGrowthPercent',
            'pendingEarningsGrowthPercent',
            'salesText',
            'salesGrowthPercent',
            'salesGrowthPeriod'
        ));
    }

    public function network()
    {
        $user = Auth::user();
        $networkTree = $this->buildBinaryTree($user, 0, 10);

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

    private function buildBinaryTree($user, $depth = 0, $maxDepth = 3)
    {
        if ($depth >= $maxDepth) {
            return null;
        }

        $binaryTree = \App\Models\BinaryTree::where('user_id', $user->id)->first();
        $left_volume = $binaryTree ? $binaryTree->left_volume : 0;
        $right_volume = $binaryTree ? $binaryTree->right_volume : 0;
        $left_child_id = $binaryTree ? $binaryTree->left_child_id : null;
        $right_child_id = $binaryTree ? $binaryTree->right_child_id : null;

        $node = [
            'name' => $user->name,
            'id' => $user->id,
            'level' => $depth + 1,
            'left_volume' => $left_volume,
            'right_volume' => $right_volume,
            'profile_image' => $user->profile_image,
            'children' => [null, null] // Initialize with null placeholders for left and right
        ];

        // Left child
        if ($left_child_id) {
            $leftUser = \App\Models\User::find($left_child_id);
            if ($leftUser) {
                $leftChild = $this->buildBinaryTree($leftUser, $depth + 1, $maxDepth);
                if ($leftChild) {
                    $node['children'][0] = $leftChild; // Left child at index 0
                }
            }
        }

        // Right child
        if ($right_child_id) {
            $rightUser = \App\Models\User::find($right_child_id);
            if ($rightUser) {
                $rightChild = $this->buildBinaryTree($rightUser, $depth + 1, $maxDepth);
                if ($rightChild) {
                    $node['children'][1] = $rightChild; // Right child at index 1
                }
            }
        }

        return $node;
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

    public function notification()
    {
        $user = Auth::user();
        $notificationService = new NotificationService();

        // Get paginated notifications for the user
        $notifications = $notificationService->getUserNotifications($user->id, 15);

        // If no notifications exist, create some sample ones for demonstration
        if ($notifications->isEmpty()) {
            $this->createSampleNotifications($user, $notificationService);
            $notifications = $notificationService->getUserNotifications($user->id, 15);
        }

        return view('DashboardNotification', compact('notifications'));
    }

    private function createSampleNotifications(User $user, NotificationService $notificationService)
    {
        // Welcome notification
        $notificationService->createNotification(
            $user->id,
            'success',
            'Welcome to AKEN MLM!',
            'Your account has been successfully created. Start building your network today!',
            'rocket',
            'success'
        );

        // Profile completion reminder
        if (!$user->phone || !$user->address) {
            $notificationService->createNotification(
                $user->id,
                'info',
                'Complete Your Profile',
                'Please complete your profile information to unlock all features.',
                'user-edit',
                'info'
            );
        }

        // Referral code notification
        $notificationService->createNotification(
            $user->id,
            'info',
            'Your Referral Link is Ready',
            'Share your referral link to start earning commissions: ' . url('/register?ref=' . $user->referral_code),
            'link',
            'primary'
        );

        // Network building tips
        $downlinesCount = User::where('sponsor_id', $user->id)->count();
        if ($downlinesCount == 0) {
            $notificationService->createNotification(
                $user->id,
                'info',
                'Start Building Your Network',
                'Add your first referral to begin earning from the binary compensation plan.',
                'users',
                'primary'
            );
        }

        // Recent earnings notification (if any)
        $recentEarnings = Earning::where('user_id', $user->id)->latest()->first();
        if ($recentEarnings) {
            $notificationService->notifyEarnings($user, $recentEarnings);
        }

        // Pending withdrawals notification
        $pendingWithdrawals = Withdrawal::where('user_id', $user->id)->where('status', 'pending')->count();
        if ($pendingWithdrawals > 0) {
            $notificationService->createNotification(
                $user->id,
                'info',
                'Withdrawal Pending Review',
                "You have {$pendingWithdrawals} withdrawal request(s) currently being reviewed.",
                'clock',
                'warning'
            );
        }
    }
}