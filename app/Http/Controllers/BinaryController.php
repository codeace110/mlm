<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BinaryBalancerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BinaryController extends Controller
{
    protected BinaryBalancerService $balancerService;

    public function __construct(BinaryBalancerService $balancerService)
    {
        $this->balancerService = $balancerService;
    }

    /**
     * Place a new user in the binary tree.
     */
    public function placeUser(Request $request): JsonResponse
    {
        $request->validate([
            'new_user_id' => 'required|string|exists:users,id',
            'sponsor_id' => 'required|string|exists:users,id',
            'preferred_side' => 'nullable|string|in:left,right',
        ]);

        try {
            $newUser = User::findOrFail($request->new_user_id);
            $sponsor = User::findOrFail($request->sponsor_id);

            $this->balancerService->placeUser($newUser, $sponsor, $request->preferred_side);

            return response()->json([
                'success' => true,
                'message' => 'User placed successfully in binary tree',
                'data' => [
                    'new_user' => $newUser->id,
                    'sponsor' => $sponsor->id,
                    'placement_side' => $newUser->placement_side,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to place user: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get user's binary tree information.
     */
    public function getTreeInfo(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|string|exists:users,id',
        ]);

        $user = User::with('binaryTree')->findOrFail($request->user_id);
        $tree = $user->binaryTree;

        if (!$tree) {
            return response()->json([
                'success' => false,
                'message' => 'Binary tree not found for user'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'total_left_volume' => $tree->total_left_volume,
                'total_right_volume' => $tree->total_right_volume,
                'left_consumed' => $tree->left_consumed,
                'right_consumed' => $tree->right_consumed,
                'level_index' => $tree->level_index,
                'reward_count' => $tree->reward_count,
                'direct_pairs_paid' => $tree->direct_pairs_paid,
                'current_quota' => 2 ** $tree->level_index,
                'effective_left' => $tree->total_left_volume - $tree->left_consumed,
                'effective_right' => $tree->total_right_volume - $tree->right_consumed,
            ]
        ]);
    }

    /**
     * Manually trigger level processing for a user.
     */
    public function processLevels(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|string|exists:users,id',
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            $this->balancerService->processLevels($user);

            return response()->json([
                'success' => true,
                'message' => 'Levels processed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process levels: ' . $e->getMessage()
            ], 400);
        }
    }
}