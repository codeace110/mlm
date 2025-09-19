<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Earning;
use App\Models\Withdrawal;
use App\Models\ReferralCode;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use App\Services\DashboardService;
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

        $dashboardService = new DashboardService();
        $dashboardData = $dashboardService->getDashboardData($user);

        // Calculate growth percentages (simplified - in real app, compare with previous period)
        $downlineGrowthPercent = '+0%'; // Placeholder
        $balanceGrowthPercent = '+0%'; // Placeholder
        $withdrawalGrowthPercent = '+0%'; // Placeholder
        $pendingEarningsGrowthPercent = '+0%'; // Placeholder
        $salesText = 'Sales data will be displayed here'; // Placeholder
        $salesGrowthPercent = '0%'; // Placeholder
        $salesGrowthPeriod = 'this period'; // Placeholder

        return view('dashboard', array_merge($dashboardData, [
            'user' => $user,
            'downlineGrowthPercent' => $downlineGrowthPercent,
            'balanceGrowthPercent' => $balanceGrowthPercent,
            'withdrawalGrowthPercent' => $withdrawalGrowthPercent,
            'pendingEarningsGrowthPercent' => $pendingEarningsGrowthPercent,
            'salesText' => $salesText,
            'salesGrowthPercent' => $salesGrowthPercent,
            'salesGrowthPeriod' => $salesGrowthPeriod
        ]));
    }

    public function network()
    {
        $user = Auth::user();
        $binaryTreeService = new BinaryTreeService();
        $networkTree = $binaryTreeService->buildBinaryTreeForView($user, 0, 10);

        return view('dashboard-network', compact('networkTree'));
    }


    public function ajaxChartData()
    {
        $user = Auth::user();
        $dashboardService = new DashboardService();

        $earningsData = $dashboardService->getEarningsChartData($user);
        $networkData = $dashboardService->getNetworkChartData($user);

        return response()->json([
            'success' => true,
            'earnings' => $earningsData,
            'network' => $networkData
        ]);
    }

    public function ajaxEarningsByType()
    {
        $user = Auth::user();
        $dashboardService = new DashboardService();
        $earningsData = $dashboardService->getEarningsByTypeData($user);

        return response()->json([
            'success' => true,
            'labels' => $earningsData['labels'],
            'data' => $earningsData['data']
        ]);
    }

    public function ajaxNetworkStats()
    {
        $user = Auth::user();
        $binaryTreeService = new BinaryTreeService();
        $networkTree = $binaryTreeService->buildBinaryTreeForView($user, 0, 10);

        // Calculate network statistics
        $level1Count = $networkTree['children'] ? count($networkTree['children']) : 0;
        $level2Count = 0;
        $level3Count = 0;

        if ($networkTree['children']) {
            $networkTree['children']->each(function($child) use (&$level2Count, &$level3Count) {
                if ($child['children']) {
                    $level2Count += count($child['children']);
                    $child['children']->each(function($grandchild) use (&$level3Count) {
                        if ($grandchild['children']) {
                            $level3Count += count($grandchild['children']);
                        }
                    });
                }
            });
        }

        return response()->json([
            'success' => true,
            'stats' => [
                'level1' => $level1Count,
                'level2' => $level2Count,
                'level3' => $level3Count,
                'total' => $level1Count + $level2Count + $level3Count
            ]
        ]);
    }

    public function ajaxBalanceStats()
    {
        $user = Auth::user();
        $dashboardService = new DashboardService();
        $dashboardData = $dashboardService->getDashboardData($user);

        return response()->json([
            'success' => true,
            'balance' => $dashboardData['balance'],
            'totalEarnings' => $dashboardData['totalEarnings'],
            'pendingEarnings' => $dashboardData['pendingEarnings'],
            'totalWithdrawals' => $dashboardData['totalWithdrawals']
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
            NotificationService::createSampleNotifications($user);
            $notifications = $notificationService->getUserNotifications((int) $user->id, 15);
        }

        return view('dashboard-notification', compact('notifications'));
    }

    public function ajaxNetworkData(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', '30d'); // 7d, 30d, 90d

        $dashboardService = new DashboardService();
        $networkData = $dashboardService->getNetworkDataForPeriod($user, $period);

        return response()->json([
            'success' => true,
            'networkData' => $networkData
        ]);
    }

    public function ajaxSalesData(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', 'monthly'); // weekly, monthly, yearly

        $dashboardService = new DashboardService();
        $salesData = $dashboardService->getSalesDataForPeriod($user, $period);

        return response()->json([
            'success' => true,
            'salesData' => $salesData
        ]);
    }

    public function ajaxEarningsBreakdown()
    {
        $user = Auth::user();
        $dashboardService = new DashboardService();
        $earningsBreakdown = $dashboardService->getEarningsBreakdownData($user);

        return response()->json([
            'success' => true,
            'earningsBreakdown' => $earningsBreakdown
        ]);
    }

    public function ajaxBalanceData()
    {
        $user = Auth::user();
        $dashboardService = new DashboardService();
        $balanceData = $dashboardService->getBalanceTrendData($user);

        return response()->json([
            'success' => true,
            'balanceData' => $balanceData
        ]);
    }

}