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
            'left_volume' => 0,
            'right_volume' => 0,
            'carryover_left' => 0,
            'carryover_right' => 0,
        ];
        BinaryTree::firstOrCreate(['user_id' => $user->id], $defaults);
    }

    /**
     * Recursively place user in the tree with spillover.
     */
    private function placeRecursively(User $current, User $newUser, ?string $preferredSide = null): bool
    {
        $defaults = [
            'left_volume' => 0,
            'right_volume' => 0,
            'carryover_left' => 0,
            'carryover_right' => 0,
        ];
        $tree = BinaryTree::firstOrCreate(['user_id' => $current->id], $defaults);

        if ($preferredSide === 'left' && !$tree->left_child_id) {
            $tree->update([
                'left_child_id' => $newUser->id
            ]);
            return true;
        } elseif ($preferredSide === 'right' && !$tree->right_child_id) {
            $tree->update([
                'right_child_id' => $newUser->id
            ]);
            return true;
        } elseif (!$tree->left_child_id) {
            $tree->update([
                'left_child_id' => $newUser->id
            ]);
            return true;
        } elseif (!$tree->right_child_id) {
            $tree->update([
                'right_child_id' => $newUser->id
            ]);
            return true;
        } else {
            $weakerSide = $preferredSide ?: $this->getWeakerLeg($tree);
            $childId = $weakerSide === 'left' ? $tree->left_child_id : $tree->right_child_id;
            $childUser = User::find($childId);

            if ($childUser && $this->placeRecursively($childUser, $newUser, $weakerSide)) {
                $leg = $weakerSide . '_volume';
                $totalLeg = 'total_' . $weakerSide . '_volume';
                $tree->update([
                    $leg => ((float) $tree->{$leg}) + $this->volumePerRecruit,
                    $totalLeg => ((float) $tree->{$totalLeg}) + $this->volumePerRecruit
                ]);
                return true;
            }

            // Try the other side
            $otherSide = $weakerSide === 'left' ? 'right' : 'left';
            $otherChildId = $otherSide === 'left' ? $tree->left_child_id : $tree->right_child_id;
            $otherChildUser = User::find($otherChildId);
            if ($otherChildUser && $this->placeRecursively($otherChildUser, $newUser, $otherSide)) {
                $leg = $otherSide . '_volume';
                $totalLeg = 'total_' . $otherSide . '_volume';
                $tree->update([
                    $leg => ((float) $tree->{$leg}) + $this->volumePerRecruit,
                    $totalLeg => ((float) $tree->{$totalLeg}) + $this->volumePerRecruit
                ]);
                return true;
            }

            return false;
        }
    }

    /**
     * Get the weaker leg (lower volume) for spillover.
     */
    private function getWeakerLeg(BinaryTree $tree): string
    {
        $leftVol = (float) ($tree->left_volume ?? 0);
        $rightVol = (float) ($tree->right_volume ?? 0);
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
        while ($current->sponsor_id) {
            $sponsorId = $current->sponsor_id;
            $sponsor = User::find($sponsorId);
            $side = $current->placement_side;
            if ($side) {
                $leg = $side . '_volume';
                $totalLeg = 'total_' . $side . '_volume';
                $sponsorTree = BinaryTree::where('user_id', $sponsor->id)->first();
                if ($sponsorTree) {
                    $sponsorTree->update([
                        $leg => ((float) $sponsorTree->getAttribute($leg)) + $volume,
                        $totalLeg => ((float) $sponsorTree->getAttribute($totalLeg)) + $volume
                    ]);
                }
            }
            $current = $sponsor;
        }
    }

    /**
     * Award direct referral bonus instantly.
     */
    private function awardDirectBonus(User $sponsor, User $newUser)
    {
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

    /**
     * Process balancer for pair matching, carryovers, and product rewards.
     */
    public function processBalancer(User $user)
    {
        $tree = BinaryTree::where('user_id', $user->id)->first();
        if (!$tree) return;

        $leftVol = (float) $tree->left_volume;
        $rightVol = (float) $tree->right_volume;
        $carryoverLeft = (float) $tree->carryover_left;
        $carryoverRight = (float) $tree->carryover_right;

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
            'left_volume' => $newLeftVol,
            'right_volume' => $newRightVol,
            'carryover_left' => $newCarryoverLeft,
            'carryover_right' => $newCarryoverRight,
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
            'left_volume' => $tree ? $tree->left_volume : 0,
            'right_volume' => $tree ? $tree->right_volume : 0,
            'carryover_left' => $tree ? $tree->carryover_left : 0,
            'carryover_right' => $tree ? $tree->carryover_right : 0,
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
            $total_left_volume = $binaryTree->total_left_volume;
            $total_right_volume = $binaryTree->total_right_volume;
            $left_consumed = $binaryTree->left_consumed;
            $right_consumed = $binaryTree->right_consumed;
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

        // Calculate effective volumes (carryover)
        $effective_left = $total_left_volume - $left_consumed;
        $effective_right = $total_right_volume - $right_consumed;

        $node = [
            'name' => $user->name,
            'id' => $user->id,
            'level' => $depth + 1,
            'left_volume' => $total_left_volume, // Keep for backward compatibility in view
            'right_volume' => $total_right_volume,
            'carryover_left' => $effective_left, // Effective = carryover
            'carryover_right' => $effective_right,
            'profile_image' => $user->profile_image,
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
}