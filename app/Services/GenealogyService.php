<?php

namespace App\Services;

use App\Models\User;
use App\Models\BinaryTree;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Genealogy Service
 *
 * Provides comprehensive genealogy and network visualization functionality
 * for the MLM binary tree structure.
 */
class GenealogyService
{
    /**
     * Get complete genealogy tree for a user
     */
    public function getGenealogyTree(User $user, int $maxDepth = 10): array
    {
        $tree = BinaryTree::where('user_id', $user->id)->first();

        if (!$tree) {
            return [
                'user' => $user,
                'tree' => null,
                'children' => [],
                'stats' => $this->getUserNetworkStats($user),
            ];
        }

        return [
            'user' => $user,
            'tree' => $tree,
            'children' => $this->buildTreeChildren($user, 0, $maxDepth),
            'stats' => $this->getUserNetworkStats($user),
        ];
    }

    /**
     * Build tree children recursively
     */
    private function buildTreeChildren(User $user, int $depth, int $maxDepth): array
    {
        if ($depth >= $maxDepth) {
            return [];
        }

        $tree = BinaryTree::where('user_id', $user->id)->first();
        $children = [];

        if ($tree) {
            // Left child
            if ($tree->left_child_id) {
                $leftChild = User::find($tree->left_child_id);
                if ($leftChild) {
                    $children['left'] = [
                        'user' => $leftChild,
                        'tree' => BinaryTree::where('user_id', $leftChild->id)->first(),
                        'children' => $this->buildTreeChildren($leftChild, $depth + 1, $maxDepth),
                        'placement_side' => 'left',
                    ];
                }
            }

            // Right child
            if ($tree->right_child_id) {
                $rightChild = User::find($tree->right_child_id);
                if ($rightChild) {
                    $children['right'] = [
                        'user' => $rightChild,
                        'tree' => BinaryTree::where('user_id', $rightChild->id)->first(),
                        'children' => $this->buildTreeChildren($rightChild, $depth + 1, $maxDepth),
                        'placement_side' => 'right',
                    ];
                }
            }
        }

        return $children;
    }

    /**
     * Get network statistics for a user
     */
    public function getUserNetworkStats(User $user): array
    {
        $tree = BinaryTree::where('user_id', $user->id)->first();

        if (!$tree) {
            return [
                'total_downlines' => 0,
                'left_downlines' => 0,
                'right_downlines' => 0,
                'total_volume' => 0,
                'left_volume' => 0,
                'right_volume' => 0,
                'current_level' => 1,
                'total_earnings' => $user->totalEarnings(),
                'pending_bonuses' => $user->bonuses()->where('status', 'pending')->count(),
                'paid_bonuses' => $user->bonuses()->where('status', 'paid')->count(),
            ];
        }

        $downlineStats = $this->calculateDownlineStats($user);

        return [
            'total_downlines' => $downlineStats['total'],
            'left_downlines' => $downlineStats['left'],
            'right_downlines' => $downlineStats['right'],
            'total_volume' => $tree->total_left_volume + $tree->total_right_volume,
            'left_volume' => $tree->total_left_volume,
            'right_volume' => $tree->total_right_volume,
            'current_level' => $tree->level_index,
            'total_earnings' => $user->totalEarnings(),
            'pending_bonuses' => $user->bonuses()->where('status', 'pending')->count(),
            'paid_bonuses' => $user->bonuses()->where('status', 'paid')->count(),
            'weak_leg' => $tree->total_left_volume <= $tree->total_right_volume ? 'left' : 'right',
            'strong_leg' => $tree->total_left_volume > $tree->total_right_volume ? 'left' : 'right',
        ];
    }

    /**
     * Calculate downline statistics
     */
    private function calculateDownlineStats(User $user): array
    {
        return DB::transaction(function() use ($user) {
            $leftCount = $this->countDownlineSide($user, 'left');
            $rightCount = $this->countDownlineSide($user, 'right');

            return [
                'total' => $leftCount + $rightCount,
                'left' => $leftCount,
                'right' => $rightCount,
            ];
        });
    }

    /**
     * Count downline on a specific side
     */
    private function countDownlineSide(User $user, string $side): int
    {
        $tree = BinaryTree::where('user_id', $user->id)->first();

        if (!$tree) {
            return 0;
        }

        $childColumn = $side === 'left' ? 'left_child_id' : 'right_child_id';
        $childId = $tree->$childColumn;

        if (!$childId) {
            return 0;
        }

        $child = User::find($childId);
        if (!$child) {
            return 0;
        }

        // Count this child plus all their downlines
        return 1 + $this->countAllDownlines($child);
    }

    /**
     * Count all downlines recursively
     */
    private function countAllDownlines(User $user): int
    {
        $tree = BinaryTree::where('user_id', $user->id)->first();

        if (!$tree) {
            return 0;
        }

        $count = 0;

        // Count direct children
        if ($tree->left_child_id) {
            $count++;
            $leftChild = User::find($tree->left_child_id);
            if ($leftChild) {
                $count += $this->countAllDownlines($leftChild);
            }
        }

        if ($tree->right_child_id) {
            $count++;
            $rightChild = User::find($tree->right_child_id);
            if ($rightChild) {
                $count += $this->countAllDownlines($rightChild);
            }
        }

        return $count;
    }

    /**
     * Search users for genealogy
     */
    public function searchUsers(string $query): Collection
    {
        return User::where('is_admin', false)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('referral_code', 'LIKE', "%{$query}%");
            })
            ->with(['binaryTree', 'sponsor'])
            ->limit(20)
            ->get();
    }

    /**
     * Get network data for visualization
     */
    public function getNetworkVisualizationData(User $user, int $maxLevels = 5): array
    {
        $genealogy = $this->getGenealogyTree($user, $maxLevels);

        return [
            'nodes' => $this->extractNodesForVisualization($genealogy),
            'edges' => $this->extractEdgesForVisualization($genealogy),
            'stats' => $genealogy['stats'],
        ];
    }

    /**
     * Extract nodes for visualization
     */
    private function extractNodesForVisualization(array $genealogy, array &$nodes = [], string $parentId = null): array
    {
        $user = $genealogy['user'];
        $nodeId = 'user_' . $user->id;

        $nodes[$nodeId] = [
            'id' => $nodeId,
            'label' => $user->name,
            'email' => $user->email,
            'level' => $genealogy['stats']['current_level'] ?? 1,
            'volume' => $genealogy['stats']['total_volume'] ?? 0,
            'title' => $user->name . ' (' . $user->email . ')',
            'parent_id' => $parentId,
        ];

        // Process children
        foreach ($genealogy['children'] as $side => $child) {
            $this->extractNodesForVisualization($child, $nodes, $nodeId);
        }

        return $nodes;
    }

    /**
     * Extract edges for visualization
     */
    private function extractEdgesForVisualization(array $genealogy, array &$edges = []): array
    {
        $user = $genealogy['user'];
        $fromId = 'user_' . $user->id;

        foreach ($genealogy['children'] as $side => $child) {
            $toId = 'user_' . $child['user']->id;

            $edges[] = [
                'from' => $fromId,
                'to' => $toId,
                'label' => strtoupper($side),
                'side' => $side,
            ];

            $this->extractEdgesForVisualization($child, $edges);
        }

        return $edges;
    }
}