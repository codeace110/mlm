<?php

namespace App\Services;

use App\Models\User;
use App\Models\BinaryTree;
use App\Models\Bonus;
use App\Models\Referral;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BinaryBalancerService implements MLM binary tree logic with exact rules:
 *
 * RULES:
 * - Direct referral pairs = ₱100 each (fixed)
 * - Spillover/carryover pairs = ₱100 each (fixed)
 * - Every 5th reward = product reward (₱0)
 * - No duplicate reward redemption (track with consumed counters)
 * - Volume propagation updates uplines level by level until root
 * - One-sided quota: if quota reached on left OR right, reward is triggered
 * - Simultaneous both sides: consume both, but single reward
 * - Always increment reward_count, check if multiple levels are consumed
 *
 * LOCKING STRATEGY:
 * - Uses lockForUpdate() for row-level locking to prevent race conditions
 * - Critical when multiple users are placed simultaneously or processing bonuses
 * - Ensures data consistency during concurrent operations
 */
class BinaryBalancerService
{
    /**
     * Volume per recruit
     */
    private $volumePerRecruit = 1;

    /**
     * Direct bonus amount (configurable)
     */
    private $directBonusAmount = 100;

    /**
     * Spillover bonus amount (configurable)
     */
    private $spilloverBonusAmount = 100;

    /**
     * Pair bonus amount (configurable)
     */
    private $pairBonusAmount = 100;

    /**
     * Product reward interval (configurable)
     */
    private $productRewardInterval = 5;

    public function __construct()
    {
        $this->loadBonusSettings();
    }

    /**
     * Load bonus settings from database
     */
    private function loadBonusSettings()
    {
        // Create default bonus settings if they don't exist
        $bonusSettings = \App\Models\BonusSettings::first();
        if (!$bonusSettings) {
            $bonusSettings = \App\Models\BonusSettings::create([
                'package_value' => 1000,
                'direct_bonus_percent' => 10, // 10% of package value
                'pair_bonus_amount' => 100,
                'balancer_ratio' => 1.0,
                'matching_bonus_percent' => 5,
            ]);
        }

        // Create default bonus rule if none exist
        $activeRule = \App\Models\BonusRule::where('is_active', true)->first();
        if (!$activeRule) {
            $activeRule = \App\Models\BonusRule::create([
                'name' => 'Default Product Reward Rule',
                'type' => 'product_reward',
                'percentage' => 5, // Every 5th reward
                'min_amount' => 0,
                'max_amount' => 10000,
                'is_active' => true,
            ]);
        }

        // Load settings
        if ($bonusSettings) {
            $this->directBonusAmount = $bonusSettings->direct_bonus_percent > 0
                ? $bonusSettings->package_value * ($bonusSettings->direct_bonus_percent / 100)
                : $bonusSettings->pair_bonus_amount ?? $this->directBonusAmount;
            $this->spilloverBonusAmount = $bonusSettings->pair_bonus_amount ?? $this->spilloverBonusAmount;
            $this->pairBonusAmount = $bonusSettings->pair_bonus_amount ?? $this->pairBonusAmount;
        }

        if ($activeRule) {
            $this->productRewardInterval = $activeRule->percentage > 0 ? $activeRule->percentage : $this->productRewardInterval;
        }
    }

    /**
     * Maximum upline depth to prevent infinite loops
     */
    private $maxUplineDepth = 1000;
    /**
     * Place a new user in the binary tree with proper locking and transactions
     *
     * Uses lockForUpdate to prevent race conditions when multiple users are placed simultaneously
     */
    public function placeUser(User $newUser, User $sponsor, ?string $preferredSide = null): void
    {
        DB::transaction(function() use ($newUser, $sponsor, $preferredSide) {
            // Lock the sponsor's binary tree to prevent concurrent modifications
            $sponsorTree = BinaryTree::where('user_id', $sponsor->id)
                ->lockForUpdate()
                ->firstOrCreate([
                    'user_id' => $sponsor->id,
                    'total_left_volume' => 0,
                    'total_right_volume' => 0,
                    'left_consumed' => 0,
                    'right_consumed' => 0,
                    'level_index' => 1,
                    'reward_count' => 0,
                    'direct_pairs_paid' => 0,
                    'spillover_pairs_paid' => 0,
                    'left_spillover' => 0,
                    'right_spillover' => 0,
                ]);

            // Ensure new user has a binary tree record (locked)
            $newUserTree = BinaryTree::where('user_id', $newUser->id)
                ->lockForUpdate()
                ->firstOrCreate([
                    'user_id' => $newUser->id,
                    'total_left_volume' => 0,
                    'total_right_volume' => 0,
                    'left_consumed' => 0,
                    'right_consumed' => 0,
                    'level_index' => 1,
                    'reward_count' => 0,
                    'direct_pairs_paid' => 0,
                    'spillover_pairs_paid' => 0,
                    'left_spillover' => 0,
                    'right_spillover' => 0,
                ]);

            // Update user placement info
            $newUser->sponsor_id = $sponsor->id;
            $newUser->placement_side = $preferredSide ?? 'left';
            $newUser->save();

            // Place user in binary tree with spillover logic
            $this->placeInBinaryTree($newUser, $sponsor, $preferredSide);

            // After placement, propagate volume up the upline
            $this->propagateVolumeUp($newUser, $this->volumePerRecruit);

            // Calculate direct bonus for sponsor
            $this->calculateDirectBonus($sponsor);

            // Calculate spillover bonus for sponsor
            $this->calculateSpilloverBonus($sponsor);

            // Process downline quotas for all uplines
            $this->processDownlineQuotasForUplines($newUser);
        });
    }


    /**
     * Propagate volume up the upline level by level until root
     *
     * Updates uplines level by level with proper locking to prevent race conditions
     * Handles carryover logic for unbalanced trees
     */
    public function propagateVolumeUp(User $placedUser, int $volume = 1): void
    {
        $current = $placedUser;
        $depth = 0;

        while ($current->sponsor_id && $depth < $this->maxUplineDepth) {
            $sponsor = User::find($current->sponsor_id);

            if (!$sponsor) break;

            // Lock the sponsor's tree for update to prevent concurrent modifications
            $sponsorTree = BinaryTree::where('user_id', $sponsor->id)
                ->lockForUpdate()
                ->firstOrCreate([
                    'user_id' => $sponsor->id,
                    'total_left_volume' => 0,
                    'total_right_volume' => 0,
                    'left_consumed' => 0,
                    'right_consumed' => 0,
                    'level_index' => 1,
                    'reward_count' => 0,
                    'direct_pairs_paid' => 0,
                    'spillover_pairs_paid' => 0,
                    'left_spillover' => 0,
                    'right_spillover' => 0,
                ]);

            // Determine which side this user is on relative to their sponsor
            $position = $this->getPositionRelativeToSponsor($current, $sponsor);

            // Update the appropriate volume counter
            if ($position === 'left') {
                $sponsorTree->total_left_volume += $volume;
            } else {
                $sponsorTree->total_right_volume += $volume;
            }

            // Handle carryover logic for unbalanced trees
            $this->handleCarryover($sponsorTree);

            $sponsorTree->save();

            $current = $sponsor;
            $depth++;
        }
    }

    /**
     * Handle carryover logic for unbalanced trees
     *
     * When one side has significantly more volume, carry over excess to maintain balance
     */
    private function handleCarryover(BinaryTree $tree): void
    {
        $leftVolume = $tree->total_left_volume;
        $rightVolume = $tree->total_right_volume;

        // If left side has more volume, carry over excess to right
        if ($leftVolume > $rightVolume) {
            $excess = $leftVolume - $rightVolume;
            $carryoverAmount = min($excess, $leftVolume * 0.5); // Carry over up to 50% of left volume

            $tree->left_spillover = ($tree->left_spillover ?? 0) + $carryoverAmount;
            $tree->total_left_volume -= $carryoverAmount;
            $tree->total_right_volume += $carryoverAmount;
        }
        // If right side has more volume, carry over excess to left
        elseif ($rightVolume > $leftVolume) {
            $excess = $rightVolume - $leftVolume;
            $carryoverAmount = min($excess, $rightVolume * 0.5); // Carry over up to 50% of right volume

            $tree->right_spillover = ($tree->right_spillover ?? 0) + $carryoverAmount;
            $tree->total_right_volume -= $carryoverAmount;
            $tree->total_left_volume += $carryoverAmount;
        }
    }

    /**
     * Calculate direct referral bonus for a user (fixed ₱100 per pair)
     *
     * Uses lockForUpdate to prevent race conditions when multiple bonuses are calculated simultaneously
     */
    public function calculateDirectBonus(User $user): void
    {
        // Lock the user's binary tree for update
        $tree = BinaryTree::where('user_id', $user->id)
            ->lockForUpdate()
            ->firstOrCreate([
                'user_id' => $user->id,
                'total_left_volume' => 0,
                'total_right_volume' => 0,
                'left_consumed' => 0,
                'right_consumed' => 0,
                'level_index' => 1,
                'reward_count' => 0,
                'direct_pairs_paid' => 0,
                'spillover_pairs_paid' => 0,
                'left_spillover' => 0,
                'right_spillover' => 0,
            ]);

        // Count direct children on each side
        $directLeft = User::where('sponsor_id', $user->id)->where('placement_side', 'left')->count();
        $directRight = User::where('sponsor_id', $user->id)->where('placement_side', 'right')->count();

        // Calculate available pairs (minimum of both sides)
        $pairsAvailable = min($directLeft, $directRight);
        $newPairs = max(0, $pairsAvailable - ($tree->direct_pairs_paid ?? 0));

        // Only create bonuses when there are actual new pairs to pay
        if ($newPairs <= 0) {
            Log::info('No new pairs to pay for direct bonus', [
                'user_id' => $user->id,
                'pairs_available' => $pairsAvailable,
                'direct_pairs_paid' => $tree->direct_pairs_paid ?? 0,
            ]);
            return;
        }

        // Create bonuses for new pairs
        for ($i = 0; $i < $newPairs; $i++) {
            $pairNumber = ($tree->direct_pairs_paid ?? 0) + $i + 1;
            $isProduct = ($pairNumber % $this->productRewardInterval) === 0;

            try {
                $bonus = Bonus::create([
                    'user_id' => $user->id,
                    'amount' => $isProduct ? 0 : $this->directBonusAmount,
                    'is_product' => $isProduct,
                    'reward_type' => 'direct',
                    'pair_count' => 1,
                    'description' => $isProduct
                        ? "Direct referral product reward for {$pairNumber}th pair"
                        : "Direct referral bonus ₱100 for {$pairNumber}th pair",
                    'status' => 'pending',
                ]);

                // Increment reward count for each bonus created
                $tree->reward_count++;
            } catch (\Exception $e) {
                Log::error('Failed to create direct bonus', [
                    'user_id' => $user->id,
                    'pair_number' => $pairNumber,
                    'error' => $e->getMessage(),
                ]);
                // Continue with other bonuses even if one fails
            }
        }

        // Update the count of paid direct pairs
        $tree->direct_pairs_paid = ($tree->direct_pairs_paid ?? 0) + $newPairs;
        $tree->save();
    }

    /**
     * Calculate spillover bonus for a user (fixed ₱100 per pair, same as direct bonus)
     *
     * Uses lockForUpdate to prevent race conditions when multiple bonuses are calculated simultaneously
     */
    public function calculateSpilloverBonus(User $user): void
    {
        // Lock the user's binary tree for update
        $tree = BinaryTree::where('user_id', $user->id)
            ->lockForUpdate()
            ->firstOrCreate([
                'user_id' => $user->id,
                'total_left_volume' => 0,
                'total_right_volume' => 0,
                'left_consumed' => 0,
                'right_consumed' => 0,
                'level_index' => 1,
                'reward_count' => 0,
                'direct_pairs_paid' => 0,
                'spillover_pairs_paid' => 0,
                'left_spillover' => 0,
                'right_spillover' => 0,
            ]);

        // Count spillover children on each side (non-direct referrals)
        $spilloverLeft = $this->countSpilloverChildren($user, 'left');
        $spilloverRight = $this->countSpilloverChildren($user, 'right');

        // Calculate available spillover pairs (minimum of both sides)
        $pairsAvailable = min($spilloverLeft, $spilloverRight);
        $newPairs = max(0, $pairsAvailable - ($tree->spillover_pairs_paid ?? 0));

        // Only create bonuses when there are actual new pairs to pay
        if ($newPairs <= 0) {
            Log::info('No new spillover pairs to pay', [
                'user_id' => $user->id,
                'pairs_available' => $pairsAvailable,
                'spillover_pairs_paid' => $tree->spillover_pairs_paid ?? 0,
            ]);
            return;
        }

        // Create bonuses for new spillover pairs
        for ($i = 0; $i < $newPairs; $i++) {
            $pairNumber = ($tree->spillover_pairs_paid ?? 0) + $i + 1;
            $isProduct = ($pairNumber % $this->productRewardInterval) === 0;

            try {
                $bonus = Bonus::create([
                    'user_id' => $user->id,
                    'amount' => $isProduct ? 0 : $this->spilloverBonusAmount,
                    'is_product' => $isProduct,
                    'reward_type' => 'spillover',
                    'pair_count' => 1,
                    'description' => $isProduct
                        ? "Spillover product reward for {$pairNumber}th pair"
                        : "Spillover bonus ₱100 for {$pairNumber}th pair",
                    'status' => 'pending',
                ]);

                // Increment reward count for each bonus created
                $tree->reward_count++;
            } catch (\Exception $e) {
                Log::error('Failed to create spillover bonus', [
                    'user_id' => $user->id,
                    'pair_number' => $pairNumber,
                    'error' => $e->getMessage(),
                ]);
                // Continue with other bonuses even if one fails
            }
        }

        // Update the count of paid spillover pairs
        $tree->spillover_pairs_paid = ($tree->spillover_pairs_paid ?? 0) + $newPairs;
        $tree->save();
    }


    /**
     * Process downline quotas for all uplines with one-sided quota logic
     *
     * RULE: One-sided quota - if quota reached on left OR right, reward is triggered
     * RULE: Simultaneous both sides - consume both, but single reward
     * RULE: Always increment reward_count, check if multiple levels are consumed
     */
    public function processDownlineQuotasForUplines(User $placedUser): void
    {
        $current = $placedUser;
        $depth = 0;

        while ($current->sponsor_id && $depth < $this->maxUplineDepth) {
            $sponsor = User::find($current->sponsor_id);
            if ($sponsor) {
                $this->processLevels($sponsor);
                $current = $sponsor;
                $depth++;
            } else {
                break;
            }
        }
    }

    /**
     * Process level-based bonuses for a user with one-sided quota logic
     *
     * RULE: One-sided quota - if quota reached on left OR right, reward is triggered
     * RULE: Simultaneous both sides - consume both, but single reward
     * RULE: Always increment reward_count, check if multiple levels are consumed
     * RULE: Prevent duplicate bonus creation by checking existing bonuses
     */
    public function processLevels(User $user): void
    {
        // Lock the user's binary tree for update to prevent concurrent modifications
        $tree = BinaryTree::where('user_id', $user->id)
            ->lockForUpdate()
            ->firstOrCreate([
                'user_id' => $user->id,
                'total_left_volume' => 0,
                'total_right_volume' => 0,
                'left_consumed' => 0,
                'right_consumed' => 0,
                'level_index' => 1,
                'reward_count' => 0,
                'direct_pairs_paid' => 0,
                'spillover_pairs_paid' => 0,
                'left_spillover' => 0,
                'right_spillover' => 0,
            ]);

        if (!$tree) return;

        // Process levels until no more quotas are met
        while (true) {
            $level = (int)($tree->level_index ?? 1);
            $quota = (int)pow(2, $level); // Level 1 = 2^1 = 2, Level 2 = 2^2 = 4, etc.

            // Calculate effective volumes (total - consumed)
            $effectiveLeft = (float)$tree->total_left_volume - (float)$tree->left_consumed;
            $effectiveRight = (float)$tree->total_right_volume - (float)$tree->right_consumed;

            // Check if quota is reached on either side (one-sided quota rule)
            $leftQuotaReached = $effectiveLeft >= $quota;
            $rightQuotaReached = $effectiveRight >= $quota;

            if ($leftQuotaReached || $rightQuotaReached) {
                try {
                    // Check if bonus already exists for this level to prevent duplicates
                    $existingBonus = Bonus::where('user_id', $user->id)
                        ->where('reward_type', 'level')
                        ->where('level_index', $level)
                        ->exists();

                    if ($existingBonus) {
                        Log::info('Level bonus already exists, skipping', [
                            'user_id' => $user->id,
                            'level' => $level,
                        ]);
                        break; // Stop processing if bonus already exists
                    }

                    // Issue reward for this level
                    $this->issueReward($user, 'level', $level);

                    // Consume volumes based on which quotas were reached
                    if ($leftQuotaReached && $rightQuotaReached) {
                        // Both sides reached quota - consume both (simultaneous rule)
                        $tree->left_consumed += $quota;
                        $tree->right_consumed += $quota;
                    } elseif ($leftQuotaReached) {
                        // Only left side reached quota
                        $tree->left_consumed += $quota;
                    } else {
                        // Only right side reached quota
                        $tree->right_consumed += $quota;
                    }

                    // Always increment level_index and save
                    $tree->level_index = $level + 1;
                    $tree->save();
                    continue; // Check for more levels
                } catch (\Exception $e) {
                    Log::error('Failed to process level bonus', [
                        'user_id' => $user->id,
                        'level' => $level,
                        'error' => $e->getMessage(),
                    ]);
                    break; // Stop processing if there's an error
                }
            }

            break; // No more quotas reached
        }
    }

    /**
     * Issue a reward to a user with every 5th reward being a product reward
     *
     * RULE: Every 5th reward = product reward (₱0)
     * RULE: No duplicate reward redemption (track with consumed counters)
     * RULE: Always increment reward_count
     */
    public function issueReward(User $user, string $rewardType, ?int $levelIndex = null): Bonus
    {
        // Lock the user's binary tree for update to prevent concurrent modifications
        $tree = BinaryTree::where('user_id', $user->id)
            ->lockForUpdate()
            ->firstOrCreate([
                'user_id' => $user->id,
                'total_left_volume' => 0,
                'total_right_volume' => 0,
                'left_consumed' => 0,
                'right_consumed' => 0,
                'level_index' => 1,
                'reward_count' => 0,
                'direct_pairs_paid' => 0,
                'spillover_pairs_paid' => 0,
                'left_spillover' => 0,
                'right_spillover' => 0,
            ]);

        // Always increment reward_count first
        $tree->reward_count++;
        $currentRewardNumber = $tree->reward_count;

        // Every 5th reward is a product reward (₱0)
        $isProduct = ($currentRewardNumber % $this->productRewardInterval) === 0;
        $amount = $isProduct ? 0.00 : $this->pairBonusAmount;

        $tree->save();

        $bonus = Bonus::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'is_product' => $isProduct,
            'reward_type' => $rewardType,
            'level_index' => $levelIndex,
            'pair_count' => 1,
            'description' => $this->generateRewardDescription($rewardType, $levelIndex, $isProduct, $currentRewardNumber),
            'status' => 'pending',
        ]);

        return $bonus;
    }

    /**
     * Get current direct bonus amount
     */
    public function getDirectBonusAmount()
    {
        return $this->directBonusAmount;
    }

    /**
     * Get current pair bonus amount
     */
    public function getPairBonusAmount()
    {
        return $this->pairBonusAmount;
    }

    /**
     * Get current product reward interval
     */
    public function getProductRewardInterval()
    {
        return $this->productRewardInterval;
    }


    /**
     * Place user in binary tree with spillover logic
     *
     * Uses lockForUpdate to prevent race conditions during placement
     */
    private function placeInBinaryTree(User $newUser, User $sponsor, ?string $preferredSide = null): void
    {
        $preferredSide = $preferredSide ?? 'left';

        // Lock sponsor's tree for update to prevent concurrent placements
        $sponsorTree = BinaryTree::where('user_id', $sponsor->id)
            ->lockForUpdate()
            ->firstOrCreate([
                'user_id' => $sponsor->id,
                'left_spillover' => 0,
                'right_spillover' => 0,
                'total_left_volume' => 0,
                'total_right_volume' => 0,
                'left_consumed' => 0,
                'right_consumed' => 0,
                'level_index' => 1,
                'reward_count' => 0,
                'direct_pairs_paid' => 0,
                'spillover_pairs_paid' => 0,
                'placement_side' => null,
            ]);

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
     * Count total downline for a user (for spillover logic)
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
     * Count spillover children for a user (non-direct referrals)
     */
    private function countSpilloverChildren(User $user, string $side): int
    {
        $tree = BinaryTree::where('user_id', $user->id)->first();
        if (!$tree) return 0;

        $count = 0;
        $sideColumn = $side === 'left' ? 'left_child_id' : 'right_child_id';

        // Get direct children
        $directChildId = $tree->$sideColumn;
        if (!$directChildId) return 0;

        $directChild = User::find($directChildId);
        if (!$directChild) return 0;

        // Count all children under the direct child (these are spillover)
        return $this->countAllChildren($directChild);
    }

    /**
     * Count all children under a user (recursive)
     */
    private function countAllChildren(User $user): int
    {
        $tree = BinaryTree::where('user_id', $user->id)->first();
        if (!$tree) return 0;

        $count = 0;

        // Count direct children
        if ($tree->left_child_id) $count++;
        if ($tree->right_child_id) $count++;

        // Recursively count children of children
        if ($tree->left_child_id) {
            $leftChild = User::find($tree->left_child_id);
            if ($leftChild) {
                $count += $this->countAllChildren($leftChild);
            }
        }
        if ($tree->right_child_id) {
            $rightChild = User::find($tree->right_child_id);
            if ($rightChild) {
                $count += $this->countAllChildren($rightChild);
            }
        }

        return $count;
    }

    /**
     * Spillover placement to a child with proper locking
     */
    private function spilloverToChild(User $newUser, User $child, string $side): void
    {
        // Lock child's tree for update to prevent concurrent placements
        $childTree = BinaryTree::where('user_id', $child->id)
            ->lockForUpdate()
            ->firstOrCreate([
                'user_id' => $child->id,
                'total_left_volume' => 0,
                'total_right_volume' => 0,
                'left_consumed' => 0,
                'right_consumed' => 0,
                'level_index' => 1,
                'reward_count' => 0,
                'direct_pairs_paid' => 0,
                'spillover_pairs_paid' => 0,
                'left_spillover' => 0,
                'right_spillover' => 0,
            ]);

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
     *
     * Determines which side (left/right) a user is positioned under their sponsor
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
     * Generate description for reward with reward number
     */
    private function generateRewardDescription(string $type, ?int $levelIndex, bool $isProduct, int $rewardNumber): string
    {
        if ($type === 'direct') {
            return $isProduct
                ? "Direct Referral Product Reward (Reward #{$rewardNumber})"
                : "Direct Referral Bonus ₱100 (Reward #{$rewardNumber})";
        } elseif ($type === 'spillover') {
            return $isProduct
                ? "Spillover Product Reward (Reward #{$rewardNumber})"
                : "Spillover Bonus ₱100 (Reward #{$rewardNumber})";
        } elseif ($type === 'level') {
            return $isProduct
                ? "Level {$levelIndex} Product Reward (Reward #{$rewardNumber})"
                : "Level {$levelIndex} Bonus ₱100 (Reward #{$rewardNumber})";
        }

        return $isProduct
            ? "Product Reward (Reward #{$rewardNumber})"
            : "Bonus Reward ₱100 (Reward #{$rewardNumber})";
    }
}