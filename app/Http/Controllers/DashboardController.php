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

}