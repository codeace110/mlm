<?php

namespace App\Services;

use App\Models\User;
use App\Models\BinaryTree;
use App\Models\Earning;
use App\Models\BonusSettings;
use Illuminate\Support\Facades\DB;

class BalancerService
{
    public function processPairs(User $user)
    {
        DB::transaction(function () use ($user) {
            $tree = BinaryTree::lockForUpdate()->where('user_id', $user->id)->first();
            if (!$tree) return;

            $settings = BonusSettings::first();
            if (!$settings) return;

            $pairBonus = $settings->pair_bonus_amount;
            $ratio = $this->parseRatio($settings->balancer_ratio);

            // ---- 1. Direct Pairs @ 100% ----
            $directPairs = $this->calculatePairs($tree->left_volume, $tree->right_volume, $ratio);
            $directBonus = $directPairs * $pairBonus;

            if ($directBonus > 0) {
                $earning = Earning::create([
                    'user_id' => $user->id,
                    'amount' => $directBonus,
                    'type' => 'pair',
                    'description' => "Direct pair bonus: {$directPairs} pairs at 100%",
                    'status' => 'pending',
                ]);

                // Create notification for direct pair bonus
                $notificationService = new \App\Services\NotificationService();
                $notificationService->notifyPairMatching($user, $directPairs, $directBonus);

                $this->awardMatchingBonus($user, $directBonus);

                $tree->left_volume -= $directPairs * $ratio['left'];
                $tree->right_volume -= $directPairs * $ratio['right'];
            }

            // ---- 2. Spillover Pairs @ 20% ----
            $spilloverPairs = $this->calculatePairs($tree->left_spillover, $tree->right_spillover, $ratio);
            $spilloverBonus = $spilloverPairs * $pairBonus * 0.20; // 20%

            if ($spilloverBonus > 0) {
                $earning = Earning::create([
                    'user_id' => $user->id,
                    'amount' => $spilloverBonus,
                    'type' => 'spillover',
                    'description' => "Spillover pair bonus: {$spilloverPairs} pairs at 20%",
                    'status' => 'pending',
                ]);

                // Create notification for spillover bonus
                $notificationService = new \App\Services\NotificationService();
                $notificationService->createNotification(
                    $user->id,
                    'info',
                    'Spillover Bonus Earned',
                    "You earned ₱" . number_format($spilloverBonus, 2) . " from {$spilloverPairs} spillover pairs.",
                    'hand-holding-usd',
                    'info',
                    ['pairs' => $spilloverPairs, 'bonus_amount' => $spilloverBonus]
                );

                $this->awardMatchingBonus($user, $spilloverBonus);

                $tree->left_spillover -= $spilloverPairs * $ratio['left'];
                $tree->right_spillover -= $spilloverPairs * $ratio['right'];
            }

            // ---- 3. Update carryovers ----
            $tree->carryover_left = $tree->left_volume + $tree->left_spillover;
            $tree->carryover_right = $tree->right_volume + $tree->right_spillover;
            $tree->save();
        });
    }

    private function parseRatio(string $ratioStr): array
    {
        $parts = explode(':', $ratioStr);
        return ['left' => (int) $parts[0], 'right' => (int) $parts[1]];
    }

    private function calculatePairs(float $left, float $right, array $ratio): int
    {
        return min(
            floor($left / $ratio['left']),
            floor($right / $ratio['right'])
        );
    }

    private function awardMatchingBonus(User $user, float $pairAmount)
    {
        $settings = BonusSettings::first();
        if (!$settings) return;

        $matchingPercent = $settings->matching_bonus_percent;
        if ($matchingPercent <= 0) return;

        $matchingAmount = $pairAmount * ($matchingPercent / 100);

        $current = $user;
        while ($current->sponsor_id) {
            $upline = User::find($current->sponsor_id);
            if (!$upline) break;

            $matchingEarning = Earning::create([
                'user_id' => $upline->id,
                'amount' => $matchingAmount,
                'type' => 'matching',
                'description' => "Matching bonus for downline {$user->name}'s pair earnings",
                'status' => 'pending',
            ]);

            // Create notification for matching bonus
            $notificationService = new \App\Services\NotificationService();
            $notificationService->notifyMatchingBonus($upline, $user, $matchingAmount);

            $current = $upline;
        }
    }
}
