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

            // TODO: Implement full placement logic (existing placement pipeline)
            // This should place the user under the sponsor respecting preferredSide and spillover

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

            DB::transaction(function() use ($sponsor, $volume, $current) {
                $tree = BinaryTree::where('user_id', $sponsor->id)->lockForUpdate()->firstOrCreate(['user_id' => $sponsor->id]);

                // Determine position (this depends on your placement data)
                // For now, assume we have a way to determine if current is left or right of sponsor
                $position = $this->getPositionRelativeToSponsor($current, $sponsor);

                if ($position === 'left') {
                    $tree->total_left_volume += $volume;
                } else {
                    $tree->total_right_volume += $volume;
                }

                $tree->save();
            });

            $current = $sponsor;
        }
    }

    /**
     * Calculate direct referral bonus for a user
     */
    public function calculateDirectBonus(User $user): void
    {
        DB::transaction(function() use ($user) {
            $tree = BinaryTree::where('user_id', $user->id)->lockForUpdate()->firstOrCreate(['user_id' => $user->id]);

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
            $tree = BinaryTree::where('user_id', $user->id)->lockForUpdate()->first();

            if (!$tree) return;

            while (true) {
                $level = (int)$tree->level_index;
                $quota = (int)pow(2, $level);

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
            $tree = BinaryTree::where('user_id', $user->id)->lockForUpdate()->firstOrCreate(['user_id' => $user->id]);

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
     * Get position of a user relative to their sponsor
     */
    private function getPositionRelativeToSponsor(User $user, User $sponsor): string
    {
        // Use placement_side from User model
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