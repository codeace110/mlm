<?php

namespace App\Services;

use App\Models\User;
use App\Models\BinaryTree;
use App\Models\Earning;

class BinaryTreeService
{
    protected $volumePerRecruit = 100; // Configurable volume per recruit

    public function placeUserInTree(User $newUser, User $sponsor)
    {
        // Get or create binary tree for sponsor
        $tree = BinaryTree::firstOrCreate(['user_id' => $sponsor->id]);

        // Determine placement side: default left first
        $side = $this->determinePlacementSide($tree);

        if ($side === 'left') {
            $tree->left_child_id = $newUser->id;
            $tree->left_volume = (float) $tree->left_volume + $this->volumePerRecruit;
            $tree->save();
        } else {
            $tree->right_child_id = $newUser->id;
            $tree->right_volume = (float) $tree->right_volume + $this->volumePerRecruit;
            $tree->save();
        }

        // Update placement_side in user
        $newUser->update(['placement_side' => $side]);

        // Process balancer for sponsor and uplines
        $this->processBalancer($sponsor);
    }

    protected function determinePlacementSide(BinaryTree $tree)
    {
        if (!$tree->left_child_id) {
            return 'left';
        } elseif (!$tree->right_child_id) {
            return 'right';
        } else {
            // Both filled, but for simplicity, place in left subtree or something
            // For now, assume we place in left
            return 'left';
        }
    }

    protected function processBalancer(User $user)
    {
        $balancerService = new BalancerService();
        $balancerService->processPairs($user);
    }

    public function getTreeData(User $user)
    {
        $tree = BinaryTree::where('user_id', $user->id)->first();

        if (!$tree) {
            return null;
        }

        return [
            'left_child' => $tree->leftChild,
            'right_child' => $tree->rightChild,
            'left_volume' => $tree->left_volume,
            'right_volume' => $tree->right_volume,
            'carryover_left' => $tree->carryover_left,
            'carryover_right' => $tree->carryover_right,
        ];
    }
}