<?php

namespace App\Services;

use App\Models\User;
use App\Models\BinaryTree;
use App\Models\Earning;
use App\Models\BonusSettings;

class BinaryTreeService
{
    protected $volumePerRecruit = 1; // 1 volume per recruit

    public function placeUserInTree(User $newUser, User $sponsor, ?string $preferredSide = null)
    {
        $this->createTreeForUser($newUser);
        $this->createTreeForUser($sponsor);
    
        $sponsorTree = BinaryTree::where('user_id', $sponsor->id)->first();
    
        $side = null;
        $placed = false;
        
        if ($preferredSide === 'left' && !$sponsorTree->left_child_id) {
            $sponsorTree->update([
                'left_child_id' => $newUser->id,
                'left_volume' => ((float) $sponsorTree->left_volume) + $this->volumePerRecruit
            ]);
            $side = 'left';
            $placed = true;
        } elseif ($preferredSide === 'right' && !$sponsorTree->right_child_id) {
            $sponsorTree->update([
                'right_child_id' => $newUser->id,
                'right_volume' => ((float) $sponsorTree->right_volume) + $this->volumePerRecruit
            ]);
            $side = 'right';
            $placed = true;
        } elseif (!$sponsorTree->left_child_id) {
            $sponsorTree->update([
                'left_child_id' => $newUser->id,
                'left_volume' => ((float) $sponsorTree->left_volume) + $this->volumePerRecruit
            ]);
            $side = 'left';
            $placed = true;
        } elseif (!$sponsorTree->right_child_id) {
            $sponsorTree->update([
                'right_child_id' => $newUser->id,
                'right_volume' => ((float) $sponsorTree->right_volume) + $this->volumePerRecruit
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
                    $leg = $spilloverSide . '_volume';
                    $sponsorTree->update([ $leg => ((float) $sponsorTree->{$leg}) + $this->volumePerRecruit ]);
                    $side = $spilloverSide;
                    $placed = true;
                } else {
                    // Try the other leg
                    $otherSide = $spilloverSide === 'left' ? 'right' : 'left';
                    $otherChildId = $otherSide === 'left' ? $sponsorTree->left_child_id : $sponsorTree->right_child_id;
                    $otherChildUser = User::find($otherChildId);
                    if ($otherChildUser && $this->placeRecursively($otherChildUser, $newUser, $otherSide)) {
                        $leg = $otherSide . '_volume';
                        $sponsorTree->update([ $leg => ((float) $sponsorTree->{$leg}) + $this->volumePerRecruit ]);
                        $side = $otherSide;
                        $placed = true;
                    }
                }
            }
        }
    
        if ($placed && $side) {
            $newUser->update(['placement_side' => $side]);
            $this->propagateVolumeUp($newUser, $this->volumePerRecruit);
            $this->processBalancer($sponsor);
            $this->awardDirectBonus($sponsor, $newUser);
            // Note: Matching bonus is now awarded when direct referrals earn pair bonuses
        }
    }
    
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
                'left_child_id' => $newUser->id,
                'left_volume' => ((float) $tree->left_volume) + $this->volumePerRecruit
            ]);
            return true;
        } elseif ($preferredSide === 'right' && !$tree->right_child_id) {
            $tree->update([
                'right_child_id' => $newUser->id,
                'right_volume' => ((float) $tree->right_volume) + $this->volumePerRecruit
            ]);
            return true;
        } elseif (!$tree->left_child_id) {
            $tree->update([
                'left_child_id' => $newUser->id,
                'left_volume' => ((float) $tree->left_volume) + $this->volumePerRecruit
            ]);
            return true;
        } elseif (!$tree->right_child_id) {
            $tree->update([
                'right_child_id' => $newUser->id,
                'right_volume' => ((float) $tree->right_volume) + $this->volumePerRecruit
            ]);
            return true;
        } else {
            $weakerSide = $preferredSide ?: $this->getWeakerLeg($tree);
            $childId = $weakerSide === 'left' ? $tree->left_child_id : $tree->right_child_id;
            $childUser = User::find($childId);
        
            if ($childUser && $this->placeRecursively($childUser, $newUser, $weakerSide)) {
                $leg = $weakerSide . '_volume';
                $tree->update([ $leg => ((float) $tree->{$leg}) + $this->volumePerRecruit ]);
                return true;
            }
        
            // Try the other side
            $otherSide = $weakerSide === 'left' ? 'right' : 'left';
            $otherChildId = $otherSide === 'left' ? $tree->left_child_id : $tree->right_child_id;
            $otherChildUser = User::find($otherChildId);
            if ($otherChildUser && $this->placeRecursively($otherChildUser, $newUser, $otherSide)) {
                $leg = $otherSide . '_volume';
                $tree->update([ $leg => ((float) $tree->{$leg}) + $this->volumePerRecruit ]);
                return true;
            }
        
            return false;
        }
    }
    
    private function getWeakerLeg(BinaryTree $tree): string
    {
        $leftVol = (float) ($tree->left_volume ?? 0);
        $rightVol = (float) ($tree->right_volume ?? 0);
        if ($leftVol <= $rightVol) {
            return 'left';
        }
        return 'right';
    }
    
    private function propagateVolumeUp(User $user, float $volume): void
    {
        $current = $user;
        while ($current->sponsor_id) {
            $sponsorId = $current->sponsor_id;
            $sponsor = User::find($sponsorId);
            $side = $current->placement_side;
            if ($side) {
                $leg = $side . '_volume';
                $sponsorTree = BinaryTree::where('user_id', $sponsor->id)->first();
                if ($sponsorTree) {
                    $sponsorTree->update([
                        $leg => ((float) $sponsorTree->getAttribute($leg)) + $volume
                    ]);
                }
            }
            $current = $sponsor;
        }
    }

    protected function processBalancer(User $user)
    {
        $balancerService = new BalancerService();
        $balancerService->processPairs($user);
    }

    private function awardDirectBonus(User $sponsor, User $newUser)
    {
        $settings = BonusSettings::first();
        if (!$settings) {
            return;
        }

        $packageValue = $settings->package_value;
        $directPercent = $settings->direct_bonus_percent;
        $bonusAmount = $packageValue * ($directPercent / 100);

        Earning::create([
            'user_id' => $sponsor->id,
            'amount' => $bonusAmount,
            'type' => 'direct',
            'description' => "Direct referral bonus for recruiting {$newUser->name}",
            'status' => 'pending',
        ]);
    }

    public function getTreeData(User $user, int $levels = 3)
    {
        return $this->buildTree($user, $levels);
    }

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
}