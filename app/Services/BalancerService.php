<?php

namespace App\Services;

use App\Models\User;
use App\Models\BinaryTree;
use App\Models\Earning;

class BalancerService
{
    protected $pairValue = 100; // ₱100 per pair
    protected $commissionPercentage = 10; // 10% commission

    public function processPairs(User $user)
    {
        $tree = BinaryTree::where('user_id', $user->id)->first();

        if (!$tree) {
            return;
        }

        // Get current volumes including carryover
        $leftTotal = $tree->left_volume + $tree->carryover_left;
        $rightTotal = $tree->right_volume + $tree->carryover_right;

        // Determine mode from user configuration
        $mode = $user->balancing_mode ?? '1:1';

        $pairs = 0;
        $leftMultiplier = 1;
        $rightMultiplier = 1;

        if ($mode === '1:1') {
            $leftMultiplier = 1;
            $rightMultiplier = 1;
            $pairs = min($leftTotal, $rightTotal) / $this->pairValue;
        } elseif ($mode === '2:1') {
            $leftMultiplier = 2;
            $rightMultiplier = 1;
            $pairs = min($leftTotal / 2, $rightTotal) / $this->pairValue;
        } elseif ($mode === '3:1') {
            $leftMultiplier = 3;
            $rightMultiplier = 1;
            $pairs = min($leftTotal / 3, $rightTotal) / $this->pairValue;
        }

        if ($pairs > 0) {
            $pairs = floor($pairs);

            // Calculate commission
            $commission = $pairs * $this->pairValue * ($this->commissionPercentage / 100);

            // Create earning
            Earning::create([
                'user_id' => $user->id,
                'amount' => $commission,
                'type' => 'binary_pair',
                'description' => "Binary pair commission ({$mode}): {$pairs} pairs",
            ]);

            // Deduct paired volumes
            $deductLeft = $pairs * $this->pairValue * $leftMultiplier;
            $deductRight = $pairs * $this->pairValue * $rightMultiplier;

            // Update volumes to remainders (carryover now included in volumes)
            $tree->left_volume = max(0, $leftTotal - $deductLeft);
            $tree->right_volume = max(0, $rightTotal - $deductRight);

            // Reset carryovers since remainders are now in volumes
            $tree->carryover_left = 0;
            $tree->carryover_right = 0;

            $tree->save();
        }

        // Process upline recursively
        if ($user->sponsor) {
            $this->processPairs($user->sponsor);
        }
    }
}