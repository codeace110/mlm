<?php

namespace App\Services;

use App\Models\User;
use App\Models\BinaryTree;
use App\Models\Bonus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BinaryBalancerService
{
    /**
     * Place a new user in the binary tree and trigger bonus calculations
     */
    public function placeUser(User $newUser, User $sponsor, ?string $preferredSide = null): void
    {
        DB::transaction(function() use ($newUser, $sponsor, $preferredSide) {
            // Ensure BinaryTree record exists
            $tree = BinaryTree::firstOrCreate(['user_id' => $newUser->id]);

            // Update user placement info
            $newUser->sponsor_id = $sponsor->id;
            $newUser->placement_side = $preferredSide ?? 'left';
            $newUser->save();

            // Implement placement logic with spillover
            $this->placeInBinaryTree($newUser, $sponsor, $preferredSide);

            // After placement, propagate volume and calculate bonuses
            $volume = config('binary_balancer.volume_per_recruit', 1);
            $this->propagateVolumeUp($newUser, $volume);
            $this->calculateDirectBonus($sponsor);
            $this->processDownlineQuotasForUplines($newUser);
        });
    }

    /**
     * Propagate volume up the upline
     */
    public function propagateVolumeUp(User $placedUser, int $volume = 1): void
    {
        $current = $placedUser;

        while ($current->sponsor_id) {
            $sponsor = User::find($current->sponsor_id);

            $tree = BinaryTree::where('user_id', $sponsor->id)->firstOrCreate(['user_id' => $sponsor->id]);

            // Determine position (this depends on your placement data)
            // For now, assume we have a way to determine if current is left or right of sponsor
            $position = $this->getPositionRelativeToSponsor($current, $sponsor);

            if ($position === 'left') {
                $tree->total_left_volume += $volume;
            } else {
                $tree->total_right_volume += $volume;
            }

            $tree->save();

            $current = $sponsor;
        }
    }

    /**
     * Calculate direct referral bonus for a user
     */
    public function calculateDirectBonus(User $user): void
    {
        DB::transaction(function() use ($user) {
            $tree = BinaryTree::where('user_id', $user->id)->firstOrCreate(['user_id' => $user->id]);

            // Count direct children
            $directLeft = User::where('sponsor_id', $user->id)->where('placement_side', 'left')->count();
            $directRight = User::where('sponsor_id', $user->id)->where('placement_side', 'right')->count();

            $pairsAvailable = min($directLeft, $directRight);
            $newPairs = max(0, $pairsAvailable - $tree->direct_pairs_paid);

            for ($i = 0; $i < $newPairs; $i++) {
                $this->issueReward($user, 'direct');
            }

            $tree->direct_pairs_paid += $newPairs;
            $tree->save();
        });
    }

    /**
     * Process downline quotas for all uplines
     */
    public function processDownlineQuotasForUplines(User $placedUser): void
    {
        $current = $placedUser;
        $depth = 0;
        $maxDepth = config('binary_balancer.max_upline_depth', 1000); // Prevent infinite loops

        while ($current->sponsor_id && $depth < $maxDepth) {
            $sponsor = User::find($current->sponsor_id);
            $this->processLevels($sponsor);
            $current = $sponsor;
            $depth++;
        }
    }

    /**
     * Process level-based bonuses for a user
     */
    public function processLevels(User $user): void
    {
        DB::transaction(function() use ($user) {
            $tree = BinaryTree::where('user_id', $user->id)->first();

            if (!$tree) return;

            while (true) {
                $level = (int)$tree->level_index; // Current level
                $quota = (int)pow(2, $level); // Level 1 = 2^1 = 2, Level 2 = 2^2 = 4, etc.

                $effectiveLeft = (int)$tree->total_left_volume - (int)$tree->left_consumed;
                $effectiveRight = (int)$tree->total_right_volume - (int)$tree->right_consumed;

                if ($effectiveLeft >= $quota || $effectiveRight >= $quota) {
                    $this->issueReward($user, 'level', $level);

                    if ($effectiveLeft >= $quota && $effectiveRight >= $quota) {
                        $tree->left_consumed += $quota;
                        $tree->right_consumed += $quota;
                    } elseif ($effectiveLeft >= $quota) {
                        $tree->left_consumed += $quota;
                    } else {
                        $tree->right_consumed += $quota;
                    }

                    $tree->level_index++;
                    $tree->save();
                    continue;
                }

                break;
            }
        });
    }

    /**
     * Issue a reward to a user
     */
    public function issueReward(User $user, string $type, ?int $levelIndex = null): Bonus
    {
        return DB::transaction(function() use ($user, $type, $levelIndex) {
            $tree = BinaryTree::where('user_id', $user->id)->firstOrCreate(['user_id' => $user->id]);

            if ($tree) {
                $tree->reward_count++;
                $productEveryN = config('binary_balancer.product_every_n_rewards', 5);
                $isProduct = ($tree->reward_count % $productEveryN) === 0;
                $amount = $isProduct ? 0.00 : config('binary_balancer.reward_amount', 100.00);
                $tree->save();
            } else {
                $isProduct = false;
                $amount = config('binary_balancer.reward_amount', 100.00);
            }

            $bonus = Bonus::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'is_product' => $isProduct,
                'reward_type' => $type,
                'level_index' => $levelIndex,
                'pair_count' => 1,
                'description' => $this->generateRewardDescription($type, $levelIndex, $isProduct),
                'status' => 'pending',
            ]);

            // TODO: Notify user
            // NotificationService::notifyEarnings($user, $bonus);

            Log::info("Reward issued", [
                'user_id' => $user->id,
                'type' => $type,
                'level' => $levelIndex,
                'amount' => $amount,
                'is_product' => $isProduct,
            ]);

            return $bonus;
        });
    }

    /**
     * Place user in binary tree with spillover logic
     */
    private function placeInBinaryTree(User $newUser, User $sponsor, ?string $preferredSide = null): void
    {
        $preferredSide = $preferredSide ?? 'left';

        // Get sponsor's binary tree
        $sponsorTree = BinaryTree::where('user_id', $sponsor->id)->first();

        if (!$sponsorTree) {
            // First placement for sponsor
            $sponsorTree = BinaryTree::create(['user_id' => $sponsor->id]);
        }

        // Try to place in preferred position first
        if ($preferredSide === 'left' && !$sponsorTree->left_child_id) {
            $sponsorTree->left_child_id = $newUser->id;
            $newUser->placement_side = 'left';
        } elseif ($preferredSide === 'right' && !$sponsorTree->right_child_id) {
            $sponsorTree->right_child_id = $newUser->id;
            $newUser->placement_side = 'right';
        } else {
            // Spillover: place in the first available position
            if (!$sponsorTree->left_child_id) {
                $sponsorTree->left_child_id = $newUser->id;
                $newUser->placement_side = 'left';
            } elseif (!$sponsorTree->right_child_id) {
                $sponsorTree->right_child_id = $newUser->id;
                $newUser->placement_side = 'right';
            } else {
                // Both positions taken, spillover to the side with fewer children
                $leftChild = User::find($sponsorTree->left_child_id);
                $rightChild = User::find($sponsorTree->right_child_id);

                $leftCount = $this->countDownline($leftChild);
                $rightCount = $this->countDownline($rightChild);

                if ($leftCount <= $rightCount) {
                    // Spillover to left side
                    $this->spilloverToChild($newUser, $leftChild, 'left');
                } else {
                    // Spillover to right side
                    $this->spilloverToChild($newUser, $rightChild, 'right');
                }
            }
        }

        $sponsorTree->save();
        $newUser->save();
    }

    /**
     * Count total downline for a user
     */
    private function countDownline(?User $user): int
    {
        if (!$user) return 0;

        $tree = BinaryTree::where('user_id', $user->id)->first();
        if (!$tree) return 0;

        $count = 0;
        if ($tree->left_child_id) $count++;
        if ($tree->right_child_id) $count++;

        // Recursively count deeper levels
        if ($tree->left_child_id) {
            $leftChild = User::find($tree->left_child_id);
            $count += $this->countDownline($leftChild);
        }
        if ($tree->right_child_id) {
            $rightChild = User::find($tree->right_child_id);
            $count += $this->countDownline($rightChild);
        }

        return $count;
    }

    /**
     * Spillover placement to a child
     */
    private function spilloverToChild(User $newUser, User $child, string $side): void
    {
        $childTree = BinaryTree::where('user_id', $child->id)->first();

        if (!$childTree) {
            $childTree = BinaryTree::create(['user_id' => $child->id]);
        }

        // For spillover, the placement_side should be relative to the original sponsor, not the spillover parent
        $newUser->placement_side = $side;

        // Try to place in child's preferred side first
        if ($side === 'left' && !$childTree->left_child_id) {
            $childTree->left_child_id = $newUser->id;
        } elseif ($side === 'right' && !$childTree->right_child_id) {
            $childTree->right_child_id = $newUser->id;
        } else {
            // Continue spillover recursively
            if (!$childTree->left_child_id) {
                $childTree->left_child_id = $newUser->id;
            } elseif (!$childTree->right_child_id) {
                $childTree->right_child_id = $newUser->id;
            } else {
                // Both taken, go deeper
                $nextChild = $side === 'left' ? User::find($childTree->left_child_id) : User::find($childTree->right_child_id);
                if ($nextChild) {
                    $this->spilloverToChild($newUser, $nextChild, $side);
                    return; // Don't save childTree if recursing
                }
            }
        }

        $childTree->save();
    }

    /**
     * Get position of a user relative to their sponsor
      */
     private function getPositionRelativeToSponsor(User $user, User $sponsor): string
     {
         // Check if user is direct child
         $sponsorTree = BinaryTree::where('user_id', $sponsor->id)->first();
         if ($sponsorTree) {
             if ($sponsorTree->left_child_id == $user->id) {
                 return 'left';
             }
             if ($sponsorTree->right_child_id == $user->id) {
                 return 'right';
             }
         }

         // If user is directly sponsored by this sponsor, return user's placement_side
         if ($user->sponsor_id == $sponsor->id) {
             return $user->placement_side ?? 'left';
         }

         // If not direct, get the position of the direct sponsor
         $directSponsor = User::find($user->sponsor_id);
         if ($directSponsor) {
             return $this->getPositionRelativeToSponsor($directSponsor, $sponsor);
         }

         // Fallback
         return $user->placement_side ?? 'left';
     }

    /**
     * Generate description for reward
     */
    private function generateRewardDescription(string $type, ?int $levelIndex, bool $isProduct): string
    {
        if ($type === 'direct') {
            return $isProduct ? 'Direct Referral Product Reward' : 'Direct Referral Bonus';
        } elseif ($type === 'level') {
            return $isProduct ? "Level {$levelIndex} Product Reward" : "Level {$levelIndex} Bonus";
        }

        return 'Bonus Reward';
    }
}