<?php

namespace App\Services;

use App\Models\User;
use App\Models\BinaryTree;
use App\Models\Referral;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BinaryTreePlacementService
{
    /**
     * Place a new user in the binary tree with spillover handling
     */
    public function placeUser(User $newUser, User $sponsor, ?string $preferredSide = null): array
    {
        return DB::transaction(function() use ($newUser, $sponsor, $preferredSide) {
            // Ensure trees exist for both users
            $this->ensureTreeExists($newUser);
            $this->ensureTreeExists($sponsor);

            $sponsorTree = BinaryTree::where('user_id', $sponsor->id)->first();

            // Try direct placement first
            $placementResult = $this->tryDirectPlacement($sponsorTree, $newUser, $preferredSide);

            if ($placementResult['placed']) {
                $this->recordPlacementHistory($newUser, $sponsor, $placementResult['side'], $placementResult['parent_id']);
                return $placementResult;
            }

            // If direct placement failed, try spillover
            $spilloverResult = $this->trySpilloverPlacement($sponsorTree, $newUser, $preferredSide);

            if ($spilloverResult['placed']) {
                $this->recordPlacementHistory($newUser, $sponsor, $spilloverResult['side'], $spilloverResult['parent_id']);
                return $spilloverResult;
            }

            // If all else fails, force placement in the weaker leg
            $forcedResult = $this->forcePlacementInWeakerLeg($sponsorTree, $newUser);
            $this->recordPlacementHistory($newUser, $sponsor, $forcedResult['side'], $forcedResult['parent_id']);

            return $forcedResult;
        });
    }

    /**
     * Ensure BinaryTree record exists for user
     */
    private function ensureTreeExists(User $user): void
    {
        BinaryTree::firstOrCreate(
            ['user_id' => $user->id],
            [
                'left_volume' => 0,
                'right_volume' => 0,
                'left_spillover' => 0,
                'right_spillover' => 0,
                'total_left_volume' => 0,
                'total_right_volume' => 0,
                'left_consumed' => 0,
                'right_consumed' => 0,
            ]
        );
    }

    /**
     * Try to place user directly under sponsor
     */
    private function tryDirectPlacement(BinaryTree $sponsorTree, User $newUser, ?string $preferredSide): array
    {
        // Try preferred side first
        if ($preferredSide === 'left' && !$sponsorTree->left_child_id) {
            $sponsorTree->update(['left_child_id' => $newUser->id]);
            return [
                'placed' => true,
                'side' => 'left',
                'parent_id' => $sponsorTree->user_id,
                'method' => 'direct_preferred'
            ];
        }

        if ($preferredSide === 'right' && !$sponsorTree->right_child_id) {
            $sponsorTree->update(['right_child_id' => $newUser->id]);
            return [
                'placed' => true,
                'side' => 'right',
                'parent_id' => $sponsorTree->user_id,
                'method' => 'direct_preferred'
            ];
        }

        // Try first available position
        if (!$sponsorTree->left_child_id) {
            $sponsorTree->update(['left_child_id' => $newUser->id]);
            return [
                'placed' => true,
                'side' => 'left',
                'parent_id' => $sponsorTree->user_id,
                'method' => 'direct_available'
            ];
        }

        if (!$sponsorTree->right_child_id) {
            $sponsorTree->update(['right_child_id' => $newUser->id]);
            return [
                'placed' => true,
                'side' => 'right',
                'parent_id' => $sponsorTree->user_id,
                'method' => 'direct_available'
            ];
        }

        return ['placed' => false];
    }

    /**
     * Try spillover placement
     */
    private function trySpilloverPlacement(BinaryTree $sponsorTree, User $newUser, ?string $preferredSide): array
    {
        $weakerSide = $this->getWeakerLeg($sponsorTree);
        $targetSide = $preferredSide ?: $weakerSide;

        $childId = $targetSide === 'left' ? $sponsorTree->left_child_id : $sponsorTree->right_child_id;
        $childUser = User::find($childId);

        if ($childUser) {
            return $this->placeRecursively($childUser, $newUser, $targetSide, $sponsorTree->user_id);
        }

        return ['placed' => false];
    }

    /**
     * Place user recursively with spillover
     */
    private function placeRecursively(User $current, User $newUser, string $side, string $rootSponsorId): array
    {
        $tree = BinaryTree::where('user_id', $current->id)->first();

        // Try to place in current position
        if ($side === 'left' && !$tree->left_child_id) {
            $tree->update(['left_child_id' => $newUser->id]);
            return [
                'placed' => true,
                'side' => $side,
                'parent_id' => $tree->user_id,
                'method' => 'spillover_recursive'
            ];
        }

        if ($side === 'right' && !$tree->right_child_id) {
            $tree->update(['right_child_id' => $newUser->id]);
            return [
                'placed' => true,
                'side' => $side,
                'parent_id' => $tree->user_id,
                'method' => 'spillover_recursive'
            ];
        }

        // If position is taken, try the weaker leg of current user
        $weakerSide = $this->getWeakerLeg($tree);
        $childId = $weakerSide === 'left' ? $tree->left_child_id : $tree->right_child_id;
        $childUser = User::find($childId);

        if ($childUser) {
            return $this->placeRecursively($childUser, $newUser, $weakerSide, $rootSponsorId);
        }

        return ['placed' => false];
    }

    /**
     * Force placement in the weaker leg (fallback method)
     */
    private function forcePlacementInWeakerLeg(BinaryTree $sponsorTree, User $newUser): array
    {
        $weakerSide = $this->getWeakerLeg($sponsorTree);
        $childId = $weakerSide === 'left' ? $sponsorTree->left_child_id : $sponsorTree->right_child_id;
        $childUser = User::find($childId);

        if ($childUser) {
            $childTree = BinaryTree::where('user_id', $childUser->id)->first();

            if ($weakerSide === 'left' && !$childTree->left_child_id) {
                $childTree->update(['left_child_id' => $newUser->id]);
                return [
                    'placed' => true,
                    'side' => $weakerSide,
                    'parent_id' => $childTree->user_id,
                    'method' => 'forced_spillover'
                ];
            }

            if ($weakerSide === 'right' && !$childTree->right_child_id) {
                $childTree->update(['right_child_id' => $newUser->id]);
                return [
                    'placed' => true,
                    'side' => $weakerSide,
                    'parent_id' => $childTree->user_id,
                    'method' => 'forced_spillover'
                ];
            }

            // If child's position is also taken, force deeper placement
            return $this->forceDeepPlacement($childUser, $newUser, $weakerSide);
        }

        // Fallback: place directly under sponsor in weaker leg
        if ($weakerSide === 'left') {
            $sponsorTree->update(['left_child_id' => $newUser->id]);
        } else {
            $sponsorTree->update(['right_child_id' => $newUser->id]);
        }

        return [
            'placed' => true,
            'side' => $weakerSide,
            'parent_id' => $sponsorTree->user_id,
            'method' => 'forced_direct'
        ];
    }

    /**
     * Force placement deep in the tree
     */
    private function forceDeepPlacement(User $parent, User $newUser, string $side): array
    {
        $parentTree = BinaryTree::where('user_id', $parent->id)->first();

        if ($side === 'left') {
            $parentTree->update(['left_child_id' => $newUser->id]);
        } else {
            $parentTree->update(['right_child_id' => $newUser->id]);
        }

        return [
            'placed' => true,
            'side' => $side,
            'parent_id' => $parentTree->user_id,
            'method' => 'forced_deep'
        ];
    }

    /**
     * Get the weaker leg based on volume
     */
    private function getWeakerLeg(BinaryTree $tree): string
    {
        $leftVol = (float) ($tree->left_volume ?? 0) + (float) ($tree->left_spillover ?? 0);
        $rightVol = (float) ($tree->right_volume ?? 0) + (float) ($tree->right_spillover ?? 0);

        if ($leftVol <= $rightVol) {
            return 'left';
        }
        return 'right';
    }

    /**
     * Record placement history for auditing
     */
    private function recordPlacementHistory(User $newUser, User $sponsor, string $side, string $parentId): void
    {
        $newUser->update([
            'sponsor_id' => $sponsor->id,
            'placement_side' => $side
        ]);

        // Update parent tree with new child
        $parentTree = BinaryTree::where('user_id', $parentId)->first();
        if ($parentTree) {
            if ($side === 'left') {
                $parentTree->update(['left_child_id' => $newUser->id]);
            } else {
                $parentTree->update(['right_child_id' => $newUser->id]);
            }
        }

        Log::info('User placed in binary tree', [
            'user_id' => $newUser->id,
            'sponsor_id' => $sponsor->id,
            'parent_id' => $parentId,
            'side' => $side
        ]);
    }

    /**
     * Get placement statistics for a user
     */
    public function getPlacementStats(User $user): array
    {
        $tree = BinaryTree::where('user_id', $user->id)->first();

        if (!$tree) {
            return [
                'left_children' => 0,
                'right_children' => 0,
                'total_downline' => 0,
                'left_volume' => 0,
                'right_volume' => 0,
            ];
        }

        $leftChildren = $this->countChildrenInLeg($tree, 'left');
        $rightChildren = $this->countChildrenInLeg($tree, 'right');

        return [
            'left_children' => $leftChildren,
            'right_children' => $rightChildren,
            'total_downline' => $leftChildren + $rightChildren,
            'left_volume' => (float) $tree->left_volume,
            'right_volume' => (float) $tree->right_volume,
            'left_carryover' => (float) $tree->left_spillover,
            'right_carryover' => (float) $tree->right_spillover,
        ];
    }

    /**
     * Count children in a specific leg
     */
    private function countChildrenInLeg(BinaryTree $tree, string $side): int
    {
        $childId = $side === 'left' ? $tree->left_child_id : $tree->right_child_id;
        if (!$childId) return 0;

        $childUser = User::find($childId);
        if (!$childUser) return 0;

        $childTree = BinaryTree::where('user_id', $childUser->id)->first();
        if (!$childTree) return 1;

        return 1 + $this->countChildrenInLeg($childTree, 'left') + $this->countChildrenInLeg($childTree, 'right');
    }
}