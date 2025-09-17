<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BinaryBalancerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserPlacementController extends Controller
{
    private BinaryBalancerService $balancerService;

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

        $newUser = User::findOrFail($request->new_user_id);
        $sponsor = User::findOrFail($request->sponsor_id);

        // Ensure sponsor is not the same as new user
        if ($newUser->id === $sponsor->id) {
            return response()->json(['error' => 'User cannot be their own sponsor'], 400);
        }

        // Ensure new user doesn't already have a sponsor
        if ($newUser->sponsor_id) {
            return response()->json(['error' => 'User already has a sponsor'], 400);
        }

        try {
            // Set sponsor relationship
            $newUser->sponsor_id = $sponsor->id;
            $newUser->save();

            // Place user and calculate bonuses
            $this->balancerService->placeUser($newUser, $sponsor, $request->preferred_side);

            return response()->json([
                'message' => 'User placed successfully',
                'user_id' => $newUser->id,
                'sponsor_id' => $sponsor->id,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to place user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get user's binary tree information.
     */
    public function getBinaryTree(string $userId): JsonResponse
    {
        $user = User::with('binaryTree')->findOrFail($userId);

        return response()->json([
            'user_id' => $user->id,
            'binary_tree' => $user->binaryTree,
        ]);
    }

    /**
     * Get user's bonuses.
     */
    public function getBonuses(string $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $bonuses = $user->bonuses()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'user_id' => $user->id,
            'bonuses' => $bonuses,
        ]);
    }

    /**
     * Manually trigger bonus calculation for a user.
     */
    public function calculateBonuses(string $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        try {
            $this->balancerService->calculateDirectBonus($user);
            $this->balancerService->processLevels($user);

            return response()->json([
                'message' => 'Bonuses calculated successfully',
                'user_id' => $user->id,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to calculate bonuses: ' . $e->getMessage()], 500);
        }
    }
}