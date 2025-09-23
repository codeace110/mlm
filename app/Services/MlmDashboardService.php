<?php

namespace App\Services;

use App\Models\User;
use App\Models\BinaryTree;
use App\Models\ReferralCode;
use App\Models\Bonus;
use App\Models\Referral;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MlmDashboardService
{
    /**
     * Get comprehensive dashboard statistics for admin
     */
    public function getAdminDashboardStats(): array
    {
        $cacheKey = 'admin_dashboard_stats';
        $cacheTime = 300; // 5 minutes

        return Cache::remember($cacheKey, $cacheTime, function() {
            return [
                'total_users' => User::count(),
                'active_users' => User::where('status', 'active')->count(),
                'total_referral_codes' => ReferralCode::count(),
                'used_referral_codes' => ReferralCode::where('status', 'used')->count(),
                'available_referral_codes' => ReferralCode::where('status', 'available')->count(),
                'assigned_referral_codes' => ReferralCode::where('status', 'assigned')->count(),
                'total_bonuses' => Bonus::sum('amount'),
                'pending_bonuses' => Bonus::where('status', 'pending')->sum('amount'),
                'paid_bonuses' => Bonus::where('status', 'paid')->sum('amount'),
                'total_network_volume' => BinaryTree::sum('total_left_volume') + BinaryTree::sum('total_right_volume'),
                'total_carryover_volume' => BinaryTree::sum('carryover_left') + BinaryTree::sum('carryover_right'),
                'users_by_balancing_mode' => $this->getUsersByBalancingMode(),
                'recent_registrations' => $this->getRecentRegistrations(10),
                'top_earners' => $this->getTopEarners(10),
                'code_generation_trends' => $this->getCodeGenerationTrends(),
            ];
        });
    }

    /**
     * Get distributor dashboard statistics
     */
    public function getDistributorDashboardStats(User $user): array
    {
        $cacheKey = "distributor_dashboard_{$user->id}";
        $cacheTime = 60; // 1 minute

        return Cache::remember($cacheKey, $cacheTime, function() use ($user) {
            $tree = BinaryTree::where('user_id', $user->id)->first();

            return [
                'personal_info' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'join_date' => $user->created_at,
                    'balancing_mode' => $user->balancing_mode ?? '1:1',
                    'sponsor' => $user->sponsor ? $user->sponsor->name : 'None',
                ],
                'network_stats' => $this->getDistributorNetworkStats($user),
                'volume_stats' => $this->getDistributorVolumeStats($user, $tree),
                'bonus_stats' => $this->getDistributorBonusStats($user),
                'referral_codes' => $this->getDistributorReferralCodes($user),
                'downline_activity' => $this->getDownlineActivity($user),
                'potential_earnings' => $this->calculatePotentialEarnings($user),
            ];
        });
    }

    /**
     * Get network statistics for a distributor
     */
    private function getDistributorNetworkStats(User $user): array
    {
        $directReferrals = User::where('sponsor_id', $user->id)->count();
        $totalDownline = $this->countTotalDownline($user);
        $tree = BinaryTree::where('user_id', $user->id)->first();

        $leftLegCount = $tree ? $this->countLegMembers($tree, 'left') : 0;
        $rightLegCount = $tree ? $this->countLegMembers($tree, 'right') : 0;

        return [
            'direct_referrals' => $directReferrals,
            'total_downline' => $totalDownline,
            'left_leg_count' => $leftLegCount,
            'right_leg_count' => $rightLegCount,
            'left_leg_percentage' => $totalDownline > 0 ? round(($leftLegCount / $totalDownline) * 100, 2) : 0,
            'right_leg_percentage' => $totalDownline > 0 ? round(($rightLegCount / $totalDownline) * 100, 2) : 0,
        ];
    }

    /**
     * Get volume statistics for a distributor
     */
    private function getDistributorVolumeStats(User $user, ?BinaryTree $tree): array
    {
        if (!$tree) {
            return [
                'left_volume' => 0,
                'right_volume' => 0,
                'carryover_left' => 0,
                'carryover_right' => 0,
                'total_left_volume' => 0,
                'total_right_volume' => 0,
                'left_consumed' => 0,
                'right_consumed' => 0,
            ];
        }

        return [
            'left_volume' => (float) $tree->left_volume,
            'right_volume' => (float) $tree->right_volume,
            'carryover_left' => (float) $tree->carryover_left,
            'carryover_right' => (float) $tree->carryover_right,
            'total_left_volume' => (float) $tree->total_left_volume,
            'total_right_volume' => (float) $tree->total_right_volume,
            'left_consumed' => (float) $tree->left_consumed,
            'right_consumed' => (float) $tree->right_consumed,
            'effective_left' => (float) $tree->total_left_volume - (float) $tree->left_consumed,
            'effective_right' => (float) $tree->total_right_volume - (float) $tree->right_consumed,
        ];
    }

    /**
     * Get bonus statistics for a distributor
     */
    private function getDistributorBonusStats(User $user): array
    {
        $bonuses = Bonus::where('user_id', $user->id)->get();

        return [
            'total_earned' => $bonuses->sum('amount'),
            'pending_bonuses' => $bonuses->where('status', 'pending')->sum('amount'),
            'paid_bonuses' => $bonuses->where('status', 'paid')->sum('amount'),
            'product_rewards' => $bonuses->where('is_product', true)->count(),
            'direct_bonuses' => $bonuses->where('reward_type', 'direct')->sum('amount'),
            'pair_bonuses' => $bonuses->where('reward_type', 'pair')->sum('amount'),
            'level_bonuses' => $bonuses->where('reward_type', 'level')->sum('amount'),
            'recent_bonuses' => $bonuses->sortByDesc('created_at')->take(5),
        ];
    }

    /**
     * Get referral codes for a distributor
     */
    private function getDistributorReferralCodes(User $user): array
    {
        $codes = ReferralCode::where('assigned_to', $user->id)
            ->with(['usedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'assigned_codes' => $codes->count(),
            'used_codes' => $codes->where('status', 'used')->count(),
            'available_codes' => $codes->where('status', 'available')->count(),
            'codes' => $codes,
        ];
    }

    /**
     * Get downline activity for a distributor
     */
    private function getDownlineActivity(User $user): array
    {
        $recentReferrals = Referral::where('sponsor_id', $user->id)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $recentBonuses = Bonus::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return [
            'recent_referrals' => $recentReferrals,
            'recent_bonuses' => $recentBonuses,
            'active_downline_today' => $this->getActiveDownlineToday($user),
            'active_downline_week' => $this->getActiveDownlineThisWeek($user),
        ];
    }

    /**
     * Calculate potential earnings for a distributor
     */
    private function calculatePotentialEarnings(User $user): array
    {
        $balancerService = new BinaryBalancerService();
        $potentialPairs = $balancerService->calculatePotentialPairs($user);

        $pairBonusAmount = config('binary_balancer.pair_bonus_amount', 100);
        $potentialEarnings = $potentialPairs['pairs'] * $pairBonusAmount;

        return [
            'potential_pairs' => $potentialPairs['pairs'],
            'potential_earnings' => $potentialEarnings,
            'left_available' => $potentialPairs['left_available'],
            'right_available' => $potentialPairs['right_available'],
            'mode' => $potentialPairs['mode'],
            'mode_description' => $potentialPairs['mode_description'],
        ];
    }

    /**
     * Get users grouped by balancing mode
     */
    private function getUsersByBalancingMode(): array
    {
        return User::select('balancing_mode', DB::raw('count(*) as count'))
            ->groupBy('balancing_mode')
            ->pluck('count', 'balancing_mode')
            ->toArray();
    }

    /**
     * Get recent registrations
     */
    private function getRecentRegistrations(int $limit = 10): array
    {
        return User::with(['sponsor'])
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get top earners
     */
    private function getTopEarners(int $limit = 10): array
    {
        return User::with(['bonuses'])
            ->withCount('bonuses')
            ->orderByDesc('bonuses_sum_amount')
            ->take($limit)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'total_earned' => $user->bonuses->sum('amount'),
                    'bonus_count' => $user->bonuses_count,
                ];
            })
            ->toArray();
    }

    /**
     * Get code generation trends
     */
    private function getCodeGenerationTrends(): array
    {
        $trends = ReferralCode::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(30)
            ->get();

        return $trends->toArray();
    }

    /**
     * Count total downline for a user
     */
    private function countTotalDownline(User $user): int
    {
        $tree = BinaryTree::where('user_id', $user->id)->first();
        if (!$tree) return 0;

        return $this->countLegMembers($tree, 'left') + $this->countLegMembers($tree, 'right');
    }

    /**
     * Count members in a specific leg
     */
    private function countLegMembers(BinaryTree $tree, string $side): int
    {
        $childId = $side === 'left' ? $tree->left_child_id : $tree->right_child_id;
        if (!$childId) return 0;

        $childUser = User::find($childId);
        if (!$childUser) return 0;

        $childTree = BinaryTree::where('user_id', $childUser->id)->first();
        if (!$childTree) return 1;

        return 1 + $this->countLegMembers($childTree, 'left') + $this->countLegMembers($childTree, 'right');
    }

    /**
     * Get active downline today
     */
    private function getActiveDownlineToday(User $user): int
    {
        return User::where('sponsor_id', $user->id)
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Get active downline this week
     */
    private function getActiveDownlineThisWeek(User $user): int
    {
        return User::where('sponsor_id', $user->id)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
    }

    /**
     * Get binary tree structure for visualization
     */
    public function getBinaryTreeForVisualization(User $user, int $levels = 5): array
    {
        return $this->buildTreeVisualization($user, $levels);
    }

    /**
     * Build tree visualization data
     */
    private function buildTreeVisualization(User $user, int $levels, int $currentLevel = 0): ?array
    {
        if ($currentLevel >= $levels) {
            return null;
        }

        $tree = BinaryTree::where('user_id', $user->id)->first();

        $node = [
            'id' => $user->id,
            'name' => $user->name,
            'level' => $currentLevel + 1,
            'left_volume' => $tree ? (float) $tree->left_volume : 0,
            'right_volume' => $tree ? (float) $tree->right_volume : 0,
            'carryover_left' => $tree ? (float) $tree->carryover_left : 0,
            'carryover_right' => $tree ? (float) $tree->carryover_right : 0,
            'children' => [],
        ];

        // Left child
        if ($tree && $tree->left_child_id) {
            $leftUser = User::find($tree->left_child_id);
            if ($leftUser) {
                $node['children']['left'] = $this->buildTreeVisualization($leftUser, $levels, $currentLevel + 1);
            }
        }

        // Right child
        if ($tree && $tree->right_child_id) {
            $rightUser = User::find($tree->right_child_id);
            if ($rightUser) {
                $node['children']['right'] = $this->buildTreeVisualization($rightUser, $levels, $currentLevel + 1);
            }
        }

        return $node;
    }

    /**
     * Clear dashboard cache for a user
     */
    public function clearDashboardCache(User $user): void
    {
        Cache::forget("distributor_dashboard_{$user->id}");
    }

    /**
     * Clear all dashboard caches
     */
    public function clearAllDashboardCache(): void
    {
        Cache::flush();
    }
}