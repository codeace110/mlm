<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BinaryTree extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parent_id',
        'total_left_volume',
        'total_right_volume',
        'left_consumed',
        'right_consumed',
        'level_index',
        'reward_count',
        'direct_pairs_paid',
        'spillover_pairs_paid',
        'left_spillover',
        'right_spillover',
        'left_child_id',
        'right_child_id',
        'placement_side',
    ];

    protected $casts = [
        'total_left_volume' => 'integer',
        'total_right_volume' => 'integer',
        'left_consumed' => 'integer',
        'right_consumed' => 'integer',
        'level_index' => 'integer',
        'reward_count' => 'integer',
        'direct_pairs_paid' => 'integer',
        'spillover_pairs_paid' => 'integer',
        'left_spillover' => 'integer',
        'right_spillover' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Get the left child user
     */
    public function leftChild()
    {
        return $this->belongsTo(User::class, 'left_child_id');
    }

    /**
     * Get the right child user
     */
    public function rightChild()
    {
        return $this->belongsTo(User::class, 'right_child_id');
    }

    /**
     * Increment spillover pairs paid
     */
    public function incrementSpilloverPairsPaid(): bool
    {
        return $this->increment('spillover_pairs_paid');
    }

    /**
     * Update consumed volume for a specific side
     */
    public function updateConsumedVolume(string $side, int $amount): bool
    {
        $side = strtolower($side);
        $column = $side === 'left' ? 'left_consumed' : 'right_consumed';

        return $this->increment($column, $amount);
    }

    /**
     * Increment reward count
     */
    public function incrementRewardCount(): bool
    {
        return $this->increment('reward_count');
    }

    /**
     * Increment direct pairs paid
     */
    public function incrementDirectPairsPaid(): bool
    {
        return $this->increment('direct_pairs_paid');
    }

    /**
     * Get all downline users (children, grandchildren, etc.)
     */
    public function getDownlineUsers(): \Illuminate\Database\Eloquent\Collection
    {
        $leftChildren = collect();
        $rightChildren = collect();

        if ($this->left_child_id) {
            $leftChild = User::find($this->left_child_id);
            if ($leftChild) {
                $leftChildren = $leftChildren->merge([$leftChild])->merge($this->getDownlineFromUser($leftChild));
            }
        }

        if ($this->right_child_id) {
            $rightChild = User::find($this->right_child_id);
            if ($rightChild) {
                $rightChildren = $rightChildren->merge([$rightChild])->merge($this->getDownlineFromUser($rightChild));
            }
        }

        return $leftChildren->merge($rightChildren);
    }

    /**
     * Get downline from a specific user recursively
     */
    private function getDownlineFromUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        $downline = collect();
        $userTree = self::where('user_id', $user->id)->first();

        if ($userTree) {
            if ($userTree->left_child_id) {
                $leftChild = User::find($userTree->left_child_id);
                if ($leftChild) {
                    $downline = $downline->merge([$leftChild])->merge($this->getDownlineFromUser($leftChild));
                }
            }

            if ($userTree->right_child_id) {
                $rightChild = User::find($userTree->right_child_id);
                if ($rightChild) {
                    $downline = $downline->merge([$rightChild])->merge($this->getDownlineFromUser($rightChild));
                }
            }
        }

        return $downline;
    }

    /**
     * Get direct children count
     */
    public function getDirectChildrenCount(): int
    {
        $count = 0;
        if ($this->left_child_id) $count++;
        if ($this->right_child_id) $count++;
        return $count;
    }

    /**
     * Get total downline count
     */
    public function getTotalDownlineCount(): int
    {
        return $this->getDownlineUsers()->count();
    }

    /**
     * Get left leg downline
     */
    public function getLeftLegDownline(): \Illuminate\Database\Eloquent\Collection
    {
        $downline = collect();

        if ($this->left_child_id) {
            $leftChild = User::find($this->left_child_id);
            if ($leftChild) {
                $downline = $downline->merge([$leftChild])->merge($this->getDownlineFromUser($leftChild));
            }
        }

        return $downline;
    }

    /**
     * Get right leg downline
     */
    public function getRightLegDownline(): \Illuminate\Database\Eloquent\Collection
    {
        $downline = collect();

        if ($this->right_child_id) {
            $rightChild = User::find($this->right_child_id);
            if ($rightChild) {
                $downline = $downline->merge([$rightChild])->merge($this->getDownlineFromUser($rightChild));
            }
        }

        return $downline;
    }

    /**
     * Check if user can be placed on left side
     */
    public function canPlaceLeft(): bool
    {
        return $this->left_child_id === null;
    }

    /**
     * Check if user can be placed on right side
     */
    public function canPlaceRight(): bool
    {
        return $this->right_child_id === null;
    }

    /**
     * Get the weaker leg (side with fewer members)
     */
    public function getWeakerLeg(): string
    {
        $leftCount = $this->getLeftLegDownline()->count();
        $rightCount = $this->getRightLegDownline()->count();

        return $leftCount <= $rightCount ? 'left' : 'right';
    }

    /**
     * Get volume difference between legs
     */
    public function getVolumeDifference(): int
    {
        return abs($this->total_left_volume - $this->total_right_volume);
    }

    /**
     * Check if tree is balanced
     */
    public function isBalanced(): bool
    {
        $difference = $this->getVolumeDifference();
        $totalVolume = $this->total_left_volume + $this->total_right_volume;

        // Consider balanced if difference is less than 10% of total volume
        return $totalVolume > 0 ? ($difference / $totalVolume) < 0.1 : true;
    }

    /**
     * Get next level quota
     */
    public function getNextLevelQuota(): int
    {
        return pow(2, $this->level_index ?? 1);
    }

    /**
     * Check if level quota is reached
     */
    public function isLevelQuotaReached(): bool
    {
        $quota = $this->getNextLevelQuota();
        $effectiveLeft = $this->total_left_volume - $this->left_consumed;
        $effectiveRight = $this->total_right_volume - $this->right_consumed;

        return $effectiveLeft >= $quota || $effectiveRight >= $quota;
    }

    /**
     * Get user's level in the network
     */
    public function getUserLevel(): int
    {
        $level = 1;
        $currentParentId = $this->parent_id;

        while ($currentParentId) {
            $parentTree = self::where('user_id', $currentParentId)->first();
            if (!$parentTree) break;

            $level++;
            $currentParentId = $parentTree->parent_id;

            // Prevent infinite loops
            if ($level > 1000) break;
        }

        return $level;
    }

    /**
     * Get upline users
     */
    public function getUplineUsers(): \Illuminate\Database\Eloquent\Collection
    {
        $upline = collect();
        $currentParentId = $this->parent_id;

        while ($currentParentId) {
            $parent = User::find($currentParentId);
            if (!$parent) break;

            $upline->push($parent);
            $parentTree = self::where('user_id', $currentParentId)->first();
            $currentParentId = $parentTree ? $parentTree->parent_id : null;

            // Prevent infinite loops
            if ($upline->count() > 1000) break;
        }

        return $upline;
    }

    /**
     * Get network statistics
     */
    public function getNetworkStats(): array
    {
        $downline = $this->getDownlineUsers();
        $leftLeg = $this->getLeftLegDownline();
        $rightLeg = $this->getRightLegDownline();

        return [
            'total_downline' => $downline->count(),
            'left_leg_count' => $leftLeg->count(),
            'right_leg_count' => $rightLeg->count(),
            'direct_children' => $this->getDirectChildrenCount(),
            'current_level' => $this->getUserLevel(),
            'is_balanced' => $this->isBalanced(),
            'volume_difference' => $this->getVolumeDifference(),
            'next_level_quota' => $this->getNextLevelQuota(),
            'level_quota_reached' => $this->isLevelQuotaReached(),
        ];
    }
}
