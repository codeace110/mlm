<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GenealogyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Genealogy Controller
 *
 * Handles genealogy tree visualization and network management
 */
class GenealogyController extends Controller
{
    protected GenealogyService $genealogyService;

    public function __construct(GenealogyService $genealogyService)
    {
        $this->genealogyService = $genealogyService;
    }

    /**
     * Display genealogy tree for a user
     */
    public function show(User $user)
    {
        $genealogy = $this->genealogyService->getGenealogyTree($user);
        $networkData = $this->genealogyService->getNetworkVisualizationData($user);

        return view('genealogy.show', compact('genealogy', 'networkData', 'user'));
    }

    /**
     * Search users for genealogy
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $users = $this->genealogyService->searchUsers($query);

        return response()->json($users);
    }

    /**
     * Get network data for visualization
     */
    public function networkData(User $user): JsonResponse
    {
        $networkData = $this->genealogyService->getNetworkVisualizationData($user);

        return response()->json($networkData);
    }

    /**
     * Get user network statistics
     */
    public function userStats(User $user): JsonResponse
    {
        $stats = $this->genealogyService->getUserNetworkStats($user);

        return response()->json($stats);
    }

    /**
     * Export genealogy data
     */
    public function export(User $user)
    {
        $genealogy = $this->genealogyService->getGenealogyTree($user, 20); // Deep export

        $filename = "genealogy_{$user->id}_" . date('Y-m-d_H-i-s') . '.json';

        return response()->json($genealogy)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}