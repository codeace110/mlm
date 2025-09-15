<?php

namespace App\Services;

use App\Models\User;
use App\Models\BinaryTree;
use App\Models\Bonus;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BinaryBalancerService
{
    /**
     * Place a new user in the binary tree under the sponsor.
     */
    public function placeUser(User $newUser, User $sponsor, ?string $preferredSide = null): void
    {
        DB::transaction(function () use ($newUser, $sponsor, $preferredSide) {
            // Ensure binary tree records exist
            $this->ensureBinaryTreeExists($newUser);
            $this->ensureBinaryTreeExists($sponsor);

            // Handle placement logic (simplified - you may need to implement spillover)
            $this->performPlacement($newUser, $sponsor, $preferredSide);

            // Propagate volume up the tree
            $this->propagateVolumeUp($newUser, 1);

            // Calculate direct bonus for sponsor
            $this->calculateDirectBonus($sponsor);

            // Process downline quotas for uplines
            $this->processDownlineQuotasForUplines($newUser);
        });
    }

    /**
     * Propagate volume up the upline.
     */
    public function propagateVolumeUp(User $placedUser, int $volume = 1): void
    {
        $current = $placedUser;

        while ($current->sponsor_id) {
            $sponsor = User::find($current->sponsor_id);
            if (!$sponsor) break;

            DB::transaction(function() use ($sponsor, $current, $volume) {
                $sponsorTree = BinaryTree::where('user_id', $sponsor->id)->lockForUpdate()->first();
                if (!$sponsorTree) return;

                // Determine which side the placed user is on
                if ($current->placement_side === 'left') {
                    $sponsorTree->total_left_volume += $volume;
                } elseif ($current->placement_side === 'right') {
                    $sponsorTree->total_right_volume += $volume;
                }

                $sponsorTree->save();
            });

            $current = $sponsor;
        }
    }

    /**
     * Calculate direct referral bonus for sponsor.
     */
    public function calculateDirectBonus(User $user): void
    {
        DB::transaction(function() use ($user) {
            $tree = BinaryTree::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$tree) {
                $tree = $this->ensureBinaryTreeExists($user);
                $tree = BinaryTree::where('user_id', $user->id)->lockForUpdate()->first();
            }

            $directLeftCount = $user->downlines()->where('placement_side', 'left')->count();
            $directRightCount = $user->downlines()->where('placement_side', 'right')->count();

            $pairsAvailable = min($directLeftCount, $directRightCount);
            $newPairs = max(0, $pairsAvailable - $tree->direct_pairs_paid);

            for ($i = 0; $i < $newPairs; $i++) {
                $this->issueReward($user, 'direct', null);
            }

            $tree->direct_pairs_paid += $newPairs;
            $tree->save();
        });
    }

    /**
     * Process downline quotas for all uplines.
     */
    public function processDownlineQuotasForUplines(User $placedUser): void
    {
        $current = $placedUser;

        while ($current->sponsor_id) {
            $sponsor = User::find($current->sponsor_id);
            if (!$sponsor) break;

            $this->processLevels($sponsor);
            $current = $sponsor;
        }
    }

    /**
     * Process levels for a user (atomic operation).
     */
    public function processLevels(User $user): void
    {
        DB::transaction(function () use ($user) {
            $userTree = BinaryTree::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$userTree) return;

            while (true) {
                $level = $userTree->level_index;
                $quota = 2 ** $level;

                $effectiveLeft = $userTree->total_left_volume - $userTree->left_consumed;
                $effectiveRight = $userTree->total_right_volume - $userTree->right_consumed;

                if ($effectiveLeft >= $quota || $effectiveRight >= $quota) {
                    // Issue level reward to user
                    $this->issueReward($user, 'level', $level);

                    // Consume volumes
                    if ($effectiveLeft >= $quota && $effectiveRight >= $quota) {
                        // Both sides meet quota - consume from both
                        $userTree->left_consumed += $quota;
                        $userTree->right_consumed += $quota;
                    } elseif ($effectiveLeft >= $quota) {
                        $userTree->left_consumed += $quota;
                    } else {
                        $userTree->right_consumed += $quota;
                    }

                    // Advance to next level
                    $userTree->level_index += 1;
                    $userTree->save();
                } else {
                    break;
                }
            }
        });
    }

    /**
     * Issue a reward to the user.
     */
    public function issueReward(User $user, string $rewardType, ?int $levelIndex = null): void
    {
        DB::transaction(function() use ($user, $rewardType, $levelIndex) {
            $tree = BinaryTree::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$tree) return;

            $tree->reward_count++;
            $isProduct = ($tree->reward_count % 5 === 0);
            $amount = $isProduct ? 0.00 : 100.00;

            $description = match($rewardType) {
                'direct' => 'Direct referral bonus',
                'level' => "Level {$levelIndex} completion bonus",
                default => 'Bonus reward'
            };

            $bonus = Bonus::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'is_product' => $isProduct,
                'reward_type' => $rewardType,
                'level_index' => $levelIndex,
                'pair_count' => 1,
                'description' => $description,
                'status' => 'pending',
            ]);

            $tree->save();

            // Notify user
            NotificationService::notifyEarnings($user, $bonus);
        });
    }

    /**
     * Ensure binary tree record exists for user.
     */
    private function ensureBinaryTreeExists(User $user): void
    {
        BinaryTree::firstOrCreate(
            ['user_id' => $user->id],
            [
                'total_left_volume' => 0,
                'total_right_volume' => 0,
                'left_consumed' => 0,
                'right_consumed' => 0,
                'level_index' => 1,
                'reward_count' => 0,
                'direct_pairs_paid' => 0,
            ]
        );
    }

    /**
     * Perform placement logic with spillover handling.
     */
    private function performPlacement(User $newUser, User $sponsor, ?string $preferredSide = null): void
    {
        $sponsorTree = $sponsor->binaryTree;

        // Try to place directly under sponsor
        if ($preferredSide === 'left' && !$sponsorTree->left_child_id) {
            $sponsorTree->left_child_id = $newUser->id;
            $newUser->sponsor_id = $sponsor->id;
            $newUser->placement_side = 'left';
            $sponsorTree->save();
            $newUser->save();
            return;
        } elseif ($preferredSide === 'right' && !$sponsorTree->right_child_id) {
            $sponsorTree->right_child_id = $newUser->id;
            $newUser->sponsor_id = $sponsor->id;
            $newUser->placement_side = 'right';
            $sponsorTree->save();
            $newUser->save();
            return;
        } elseif (!$sponsorTree->left_child_id) {
            $sponsorTree->left_child_id = $newUser->id;
            $newUser->sponsor_id = $sponsor->id;
            $newUser->placement_side = 'left';
            $sponsorTree->save();
            $newUser->save();
            return;
        } elseif (!$sponsorTree->right_child_id) {
            $sponsorTree->right_child_id = $newUser->id;
            $newUser->sponsor_id = $sponsor->id;
            $newUser->placement_side = 'right';
            $sponsorTree->save();
            $newUser->save();
            return;
        }

        // Spillover: determine initial leg under sponsor
        $spilloverSide = $preferredSide ?: $this->getWeakerLeg($sponsorTree);
        $childId = $spilloverSide === 'left' ? $sponsorTree->left_child_id : $sponsorTree->right_child_id;
        $childUser = User::find($childId);

        if ($childUser && $this->placeRecursively($childUser, $newUser, $spilloverSide)) {
            $newUser->sponsor_id = $sponsor->id;
            $newUser->placement_side = $spilloverSide;
            $newUser->save();
            return;
        }

        // Try the other leg
        $otherSide = $spilloverSide === 'left' ? 'right' : 'left';
        $otherChildId = $otherSide === 'left' ? $sponsorTree->left_child_id : $sponsorTree->right_child_id;
        $otherChildUser = User::find($otherChildId);

        if ($otherChildUser && $this->placeRecursively($otherChildUser, $newUser, $otherSide)) {
            $newUser->sponsor_id = $sponsor->id;
            $newUser->placement_side = $otherSide;
            $newUser->save();
            return;
        }

        // If spillover fails, throw exception
        throw new \Exception('No available placement position in the binary tree');
    }

    /**
     * Get the weaker leg (lower volume) for spillover.
     */
    private function getWeakerLeg(BinaryTree $tree): string
    {
        $leftVol = $tree->total_left_volume - $tree->left_consumed;
        $rightVol = $tree->total_right_volume - $tree->right_consumed;
        return $leftVol <= $rightVol ? 'left' : 'right';
    }

    /**
     * Recursively place user in the tree with spillover.
     */
    private function placeRecursively(User $current, User $newUser, string $preferredSide): bool
    {
        $tree = $current->binaryTree;
        if (!$tree) {
            $tree = BinaryTree::firstOrCreate(['user_id' => $current->id]);
        }

        if ($preferredSide === 'left' && !$tree->left_child_id) {
            $tree->left_child_id = $newUser->id;
            $tree->save();
            return true;
        } elseif ($preferredSide === 'right' && !$tree->right_child_id) {
            $tree->right_child_id = $newUser->id;
            $tree->save();
            return true;
        } elseif (!$tree->left_child_id) {
            $tree->left_child_id = $newUser->id;
            $tree->save();
            return true;
        } elseif (!$tree->right_child_id) {
            $tree->right_child_id = $newUser->id;
            $tree->save();
            return true;
        }

        // Recurse to children
        $weakerSide = $this->getWeakerLeg($tree);
        $childId = $weakerSide === 'left' ? $tree->left_child_id : $tree->right_child_id;
        $childUser = User::find($childId);

        if ($childUser && $this->placeRecursively($childUser, $newUser, $weakerSide)) {
            return true;
        }

        // Try the other side
        $otherSide = $weakerSide === 'left' ? 'right' : 'left';
        $otherChildId = $otherSide === 'left' ? $tree->left_child_id : $tree->right_child_id;
        $otherChildUser = User::find($otherChildId);

        if ($otherChildUser && $this->placeRecursively($otherChildUser, $newUser, $otherSide)) {
            return true;
        }

        return false;
    }
}