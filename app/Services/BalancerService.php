<?php

namespace App\Services;

use App\Models\User;
use App\Models\BinaryTree;
use App\Models\Earning;
use App\Models\BonusSettings;

class BalancerService
{
    public function processPairs(User $user)
    {
        $tree = BinaryTree::where('user_id', $user->id)->first();
        if (!$tree) {
            return;
        }

        $settings = BonusSettings::first();
        if (!$settings) {
            return;
        }

        $pairBonus = $settings->pair_bonus_amount;
        $ratio = $this->parseRatio($settings->balancer_ratio);

        $pairs = $this->calculatePairs($tree->left_volume, $tree->right_volume, $ratio);
        $bonusAmount = $pairs * $pairBonus;

        if ($bonusAmount > 0) {
            Earning::create([
                'user_id' => $user->id,
                'amount' => $bonusAmount,
                'type' => 'pair',
                'description' => "Pair bonus for {$pairs} pairs with {$settings->balancer_ratio} balancer",
                'status' => 'pending',
            ]);

            // Trigger matching bonus for uplines
            $this->awardMatchingBonus($user, $bonusAmount);

            // Update volumes
            $leftUsed = min($tree->left_volume, $pairs * $ratio['left']);
            $rightUsed = min($tree->right_volume, $pairs * $ratio['right']);

            $tree->update([
                'left_volume' => $tree->left_volume - $leftUsed,
                'right_volume' => $tree->right_volume - $rightUsed,
            ]);

            // Carryover is the remaining unpaired
            $tree->carryover_left = $tree->left_volume;
            $tree->carryover_right = $tree->right_volume;
            $tree->save();
        }
    }

    private function parseRatio(string $ratioStr): array
    {
        $parts = explode(':', $ratioStr);
        return ['left' => (int) $parts[0], 'right' => (int) $parts[1]];
    }

    private function calculatePairs(float $left, float $right, array $ratio): int
    {
        $pairs = min(floor($left / $ratio['left']), floor($right / $ratio['right']));
        return $pairs;
    }

    private function awardMatchingBonus(User $user, float $pairAmount)
    {
        $settings = BonusSettings::first();
        if (!$settings) {
            return;
        }

        $matchingPercent = $settings->matching_bonus_percent;
        $matchingAmount = $pairAmount * ($matchingPercent / 100);

        $current = $user;
        while ($current->sponsor_id) {
            $upline = User::find($current->sponsor_id);
            if (!$upline) {
                break;
            }

            Earning::create([
                'user_id' => $upline->id,
                'amount' => $matchingAmount,
                'type' => 'matching',
                'description' => "Matching bonus for downline {$user->name}'s pair earnings",
                'status' => 'pending',
            ]);

            $current = $upline;
        }
    }
}