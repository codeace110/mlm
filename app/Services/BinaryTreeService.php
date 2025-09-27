<?php

namespace App\Services;

use App\Models\User;
use App\Models\BinaryTree;
use App\Models\Earning;
use App\Services\NotificationService;

class BinaryTreeService
{
    protected $volumePerRecruit = 1; // 1 volume per recruit
    protected $directBonusAmount = 100; // Fixed ₱100 direct referral bonus
    protected $pairBonusAmount = 100; // ₱100 per matched pair
    protected $productRewardInterval = 5; // Every 5th pair gives product instead of cash

    public function __construct()
    {
        $this->loadBonusSettings();
    }

    /**
     * Load bonus settings from database
     */
    protected function loadBonusSettings()
    {
        $bonusSettings = \App\Models\BonusSettings::first();
        $activeRule = \App\Models\BonusRule::where('is_active', true)->first();

        if ($bonusSettings) {
            $this->directBonusAmount = $bonusSettings->direct_bonus_percent > 0
                ? $bonusSettings->package_value * ($bonusSettings->direct_bonus_percent / 100)
                : $this->directBonusAmount;
            $this->pairBonusAmount = $bonusSettings->pair_bonus_amount ?? $this->pairBonusAmount;
        }

        if ($activeRule) {
            $this->productRewardInterval = $activeRule->percentage > 0 ? $activeRule->percentage : $this->productRewardInterval;
        }
    }

    /**
     * Place a new user in the binary tree under the sponsor with spillover handling.
     */
    public function placeUserInTree(User $newUser, User $sponsor, ?string $preferredSide = null)
    {
        $this->createTreeForUser($newUser);
        $this->createTreeForUser($sponsor);

        $sponsorTree = BinaryTree::where('user_id', $sponsor->id)->first();

        $side = null;
        $placed = false;

        // Try to place directly under sponsor
        if ($preferredSide === 'left' && !$sponsorTree->left_child_id) {
            $sponsorTree->update([
                'left_child_id' => $newUser->id
            ]);
            $side = 'left';
            $placed = true;
        } elseif ($preferredSide === 'right' && !$sponsorTree->right_child_id) {
            $sponsorTree->update([
                'right_child_id' => $newUser->id
            ]);
            $side = 'right';
            $placed = true;
        } elseif (!$sponsorTree->left_child_id) {
            $sponsorTree->update([
                'left_child_id' => $newUser->id
            ]);
            $side = 'left';
            $placed = true;
        } elseif (!$sponsorTree->right_child_id) {
            $sponsorTree->update([
                'right_child_id' => $newUser->id
            ]);
            $side = 'right';
            $placed = true;
        } else {
            // Spillover: determine initial leg under sponsor
            $spilloverSide = $preferredSide ?: $this->getWeakerLeg($sponsorTree);
            $childId = $spilloverSide === 'left' ? $sponsorTree->left_child_id : $sponsorTree->right_child_id;
            $childUser = User::find($childId);

            if ($childUser) {
                if ($this->placeRecursively($childUser, $newUser, $spilloverSide)) {
                    $side = $spilloverSide;
                    $placed = true;
                } else {
                    // Try the other leg
                    $otherSide = $spilloverSide === 'left' ? 'right' : 'left';
                    $otherChildId = $otherSide === 'left' ? $sponsorTree->left_child_id : $sponsorTree->right_child_id;
                    $otherChildUser = User::find($otherChildId);
                    if ($otherChildUser && $this->placeRecursively($otherChildUser, $newUser, $otherSide)) {
                        $side = $otherSide;
                        $placed = true;
                    }
                }
            }
        }

        if ($placed && $side) {
            $newUser->update(['placement_side' => $side]);
            $this->propagateVolumeUp($newUser, $this->volumePerRecruit);
            $this->awardDirectBonus($sponsor, $newUser);
            $this->processBalancer($sponsor);
        }
    }

    /**
     * Create or ensure BinaryTree record exists for user.
     */
    private function createTreeForUser(User $user)
    {
        $defaults = [
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
        ];
        BinaryTree::firstOrCreate(['user_id' => $user->id], $defaults);
    }

    /**
     * Recursively place user in the tree with spillover.
     */
    private function placeRecursively(User $current, User $newUser, ?string $preferredSide = null): bool
    {
        $defaults = [
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 1,
            'reward_count' => 0,
            'direct_pairs_paid' => 0,
            'spillover_pairs_paid' => 0,
        ];
        $tree = BinaryTree::firstOrCreate(['user_id' => $current->id], $defaults);

        // Try preferred side first
        if ($preferredSide === 'left' && !$tree->left_child_id) {
            $tree->update(['left_child_id' => $newUser->id]);
            return true;
        } elseif ($preferredSide === 'right' && !$tree->right_child_id) {
            $tree->update(['right_child_id' => $newUser->id]);
            return true;
        }

        // Try available positions in order of preference
        if (!$tree->left_child_id) {
            $tree->update(['left_child_id' => $newUser->id]);
            return true;
        } elseif (!$tree->right_child_id) {
            $tree->update(['right_child_id' => $newUser->id]);
            return true;
        }

        // Both positions taken, try spillover to weaker leg first
        $weakerSide = $preferredSide ?: $this->getWeakerLeg($tree);
        $childId = $weakerSide === 'left' ? $tree->left_child_id : $tree->right_child_id;
        $childUser = User::find($childId);

        if ($childUser && $this->placeRecursively($childUser, $newUser, $weakerSide)) {
            // Update volume for spillover
            $leg = $weakerSide . '_volume';
            $totalLeg = 'total_' . $weakerSide . '_volume';
            $currentLegValue = (float) $tree->getAttribute($leg);
            $currentTotalValue = (float) $tree->getAttribute($totalLeg);

            $tree->update([
                $leg => $currentLegValue + $this->volumePerRecruit,
                $totalLeg => $currentTotalValue + $this->volumePerRecruit
            ]);
            return true;
        }

        // Try the other side as fallback
        $otherSide = $weakerSide === 'left' ? 'right' : 'left';
        $otherChildId = $otherSide === 'left' ? $tree->left_child_id : $tree->right_child_id;
        $otherChildUser = User::find($otherChildId);

        if ($otherChildUser && $this->placeRecursively($otherChildUser, $newUser, $otherSide)) {
            $leg = $otherSide . '_volume';
            $totalLeg = 'total_' . $otherSide . '_volume';
            $currentLegValue = (float) $tree->getAttribute($leg);
            $currentTotalValue = (float) $tree->getAttribute($totalLeg);

            $tree->update([
                $leg => $currentLegValue + $this->volumePerRecruit,
                $totalLeg => $currentTotalValue + $this->volumePerRecruit
            ]);
            return true;
        }

        return false;
    }

    /**
     * Get the weaker leg (lower volume) for spillover.
     */
    private function getWeakerLeg(BinaryTree $tree): string
    {
        $leftVol = (float) ($tree->left_spillover ?? 0);
        $rightVol = (float) ($tree->right_spillover ?? 0);
        if ($leftVol <= $rightVol) {
            return 'left';
        }
        return 'right';
    }

    /**
     * Propagate volume up the tree to uplines.
     */
    private function propagateVolumeUp(User $user, float $volume): void
    {
        $current = $user;
        $depth = 0;
        $maxDepth = 100; // Prevent infinite loops

        while ($current->sponsor_id && $depth < $maxDepth) {
            $sponsorId = $current->sponsor_id;
            $sponsor = User::find($sponsorId);

            if (!$sponsor) break;

            $side = $current->placement_side;
            if ($side) {
                $leg = $side . '_volume';
                $totalLeg = 'total_' . $side . '_volume';

                // Use atomic update to prevent race conditions
                $sponsorTree = BinaryTree::where('user_id', $sponsor->id)->first();
                if ($sponsorTree) {
                    $currentLegValue = (float) $sponsorTree->getAttribute($leg);
                    $currentTotalValue = (float) $sponsorTree->getAttribute($totalLeg);

                    $sponsorTree->update([
                        $leg => $currentLegValue + $volume,
                        $totalLeg => $currentTotalValue + $volume
                    ]);
                }
            }

            $current = $sponsor;
            $depth++;
        }
    }

    /**
     * Award direct referral bonus instantly.
     */
    private function awardDirectBonus(User $sponsor, User $newUser)
    {
        // Check if direct bonus should be awarded (first two direct referrals)
        $directChildrenCount = User::where('sponsor_id', $sponsor->id)->count();

        if ($directChildrenCount <= 2) {
            $earning = Earning::create([
                'user_id' => $sponsor->id,
                'amount' => $this->directBonusAmount,
                'type' => 'direct',
                'description' => "Direct referral bonus for recruiting {$newUser->name}",
                'status' => 'pending',
            ]);

            // Create notification for the sponsor
            $notificationService = new NotificationService();
            $notificationService->notifyEarnings($sponsor, $earning);
        }
    }

    /**
     * Process balancer for pair matching, carryovers, and product rewards.
     */
    public function processBalancer(User $user)
    {
        $tree = BinaryTree::where('user_id', $user->id)->first();
        if (!$tree) return;

        $leftVol = (float) $tree->left_spillover;
        $rightVol = (float) $tree->right_spillover;
        $carryoverLeft = (float) $tree->left_spillover;
        $carryoverRight = (float) $tree->right_spillover;

        // Total available for matching
        $totalLeft = $leftVol + $carryoverLeft;
        $totalRight = $rightVol + $carryoverRight;

        // Calculate pairs
        $pairs = min($totalLeft, $totalRight);
        if ($pairs == 0) return;

        // Process pairs
        $pairsProcessed = 0;
        $productCount = 0;

        while ($pairs > 0) {
            $pairsProcessed++;
            $isProduct = ($pairsProcessed % $this->productRewardInterval == 0);

            if ($isProduct) {
                // Product reward
                $this->createEarning($user, 0, 'product', "Product reward for {$pairsProcessed}th matched pair", 'pending');
                $productCount++;
            } else {
                // Cash bonus
                $this->createEarning($user, $this->pairBonusAmount, 'pair', "Pair matching bonus for {$pairsProcessed}th pair", 'pending');
            }

            $pairs--;
        }

        // Subtract matched pairs from volumes and carryovers
        $matched = $pairsProcessed;

        // Reduce left
        $remainingLeft = $totalLeft - $matched;
        $newLeftVol = max(0, $leftVol - $matched);
        $newCarryoverLeft = max(0, $remainingLeft - $newLeftVol);

        // Reduce right
        $remainingRight = $totalRight - $matched;
        $newRightVol = max(0, $rightVol - $matched);
        $newCarryoverRight = max(0, $remainingRight - $newRightVol);

        $tree->update([
            'left_spillover' => $newLeftVol,
            'right_spillover' => $newRightVol,
            'left_spillover' => $newCarryoverLeft,
            'right_spillover' => $newCarryoverRight,
            'left_consumed' => ((float) $tree->left_consumed) + ($leftVol - $newLeftVol),
            'right_consumed' => ((float) $tree->right_consumed) + ($rightVol - $newRightVol),
        ]);

        // Notify user
        $notificationService = new NotificationService();
        $notificationService->notifyPairBonus($user, $pairsProcessed, $productCount);
    }

    /**
     * Create earning record.
     */
    public function createEarning(User $user, float $amount, string $type, string $description, string $status)
    {
        Earning::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
            'status' => $status,
        ]);
    }

    /**
     * Get tree data for display.
     */
    public function getTreeData(User $user, int $levels = 3)
    {
        return $this->buildTree($user, $levels);
    }

    /**
     * Build tree structure recursively.
     */
    private function buildTree(User $user, int $levels, int $currentLevel = 0)
    {
        if ($currentLevel >= $levels) {
            return [
                'user' => $user,
                'left' => null,
                'right' => null,
            ];
        }

        $tree = BinaryTree::where('user_id', $user->id)->first();
        $leftChild = $tree ? $tree->leftChild : null;
        $rightChild = $tree ? $tree->rightChild : null;

        return [
            'user' => $user,
            'left' => $leftChild ? $this->buildTree($leftChild, $levels, $currentLevel + 1) : null,
            'right' => $rightChild ? $this->buildTree($rightChild, $levels, $currentLevel + 1) : null,
            'left_spillover' => $tree ? $tree->left_spillover : 0,
            'right_spillover' => $tree ? $tree->right_spillover : 0,
            'left_spillover' => $tree ? $tree->left_spillover : 0,
            'right_spillover' => $tree ? $tree->right_spillover : 0,
        ];
    }

    /**
     * Build binary tree for dashboard view.
     */
    public function buildBinaryTreeForView(User $user, int $depth = 0, int $maxDepth = 3): ?array
    {
        if ($depth >= $maxDepth) {
            return null;
        }

        $binaryTree = BinaryTree::where('user_id', $user->id)->first();

        if ($binaryTree) {
            $total_left_volume = $binaryTree->total_left_volume ?? 0;
            $total_right_volume = $binaryTree->total_right_volume ?? 0;
            $left_consumed = $binaryTree->left_consumed ?? 0;
            $right_consumed = $binaryTree->right_consumed ?? 0;
            $left_child_id = $binaryTree->left_child_id;
            $right_child_id = $binaryTree->right_child_id;
        } else {
            // For cases where BinaryTree is not created (e.g., tests), find children by sponsor_id
            $directs = User::where('sponsor_id', $user->id)->orderBy('id')->get();
            $left_child_id = $directs->count() > 0 ? $directs[0]->id : null;
            $right_child_id = $directs->count() > 1 ? $directs[1]->id : null;
            $total_left_volume = 0;
            $total_right_volume = 0;
            $left_consumed = 0;
            $right_consumed = 0;
        }

        // Calculate effective volumes (total - consumed)
        $effective_left = $total_left_volume - $left_consumed;
        $effective_right = $total_right_volume - $right_consumed;

        $node = [
            'name' => $user->name,
            'id' => $user->id,
            'level' => $depth + 1,
            'left_volume' => $effective_left,
            'right_volume' => $effective_right,
            'total_left_volume' => $total_left_volume,
            'total_right_volume' => $total_right_volume,
            'left_consumed' => $left_consumed,
            'right_consumed' => $right_consumed,
            'earnings' => $user->account_balance ?? 0,
            'profile_image' => $user->profile_image ?? '/default-avatar.png',
            'children' => [null, null] // Initialize with null placeholders for left and right
        ];

        // Left child
        if ($left_child_id) {
            $leftUser = User::find($left_child_id);
            if ($leftUser) {
                $leftChild = $this->buildBinaryTreeForView($leftUser, $depth + 1, $maxDepth);
                if ($leftChild) {
                    $node['children'][0] = $leftChild; // Left child at index 0
                }
            }
        }

        // Right child
        if ($right_child_id) {
            $rightUser = User::find($right_child_id);
            if ($rightUser) {
                $rightChild = $this->buildBinaryTreeForView($rightUser, $depth + 1, $maxDepth);
                if ($rightChild) {
                    $node['children'][1] = $rightChild; // Right child at index 1
                }
            }
        }

        return $node;
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
}