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

        // Determine mode: for now, hardcode to 1:1
        $mode = '1:1'; // Can be configurable

        $pairs = 0;

        if ($mode === '1:1') {
            $pairs = min($leftTotal, $rightTotal) / $this->pairValue;
        } elseif ($mode === '2:1') {
            $pairs = min($leftTotal / 2, $rightTotal) / $this->pairValue;
        } elseif ($mode === '3:1') {
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
                'description' => "Binary pair commission: {$pairs} pairs",
            ]);

            // Update volumes
            if ($mode === '1:1') {
                $tree->left_volume = max(0, $leftTotal - $pairs * $this->pairValue);
                $tree->right_volume = max(0, $rightTotal - $pairs * $this->pairValue);
            } elseif ($mode === '2:1') {
                $tree->left_volume = max(0, $leftTotal - $pairs * $this->pairValue * 2);
                $tree->right_volume = max(0, $rightTotal - $pairs * $this->pairValue);
            } elseif ($mode === '3:1') {
                $tree->left_volume = max(0, $leftTotal - $pairs * $this->pairValue * 3);
                $tree->right_volume = max(0, $rightTotal - $pairs * $this->pairValue);
            }

            // Store carryover
            $tree->carryover_left = max(0, $leftTotal - $pairs * $this->pairValue * ($mode === '1:1' ? 1 : ($mode === '2:1' ? 2 : 3)));
            $tree->carryover_right = max(0, $rightTotal - $pairs * $this->pairValue);

            $tree->save();
        }

        // Process upline
        if ($user->sponsor) {
            $this->processPairs($user->sponsor);
        }
    }
}