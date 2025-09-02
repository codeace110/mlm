<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Referral;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ReferralController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get direct referrals with pagination
        $directReferrals = User::where('sponsor_id', $user->id)
            ->with('earnings')
            ->paginate(10);

        // Get network statistics
        $networkStats = $this->getNetworkStatistics($user);

        // Get recent referrals
        $recentReferrals = User::where('sponsor_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('referrals', compact('directReferrals', 'networkStats', 'recentReferrals'));
    }

    public function network()
    {
        $user = Auth::user();

        // Build network tree
        $networkTree = $this->buildNetworkTree($user);

        return view('network', compact('networkTree'));
    }

    public function ajaxNetwork(Request $request): JsonResponse
    {
        $user = Auth::user();
        $level = $request->get('level', 1);
        $maxLevel = $request->get('max_level', 3);

        $networkData = $this->getNetworkByLevel($user, $level, $maxLevel);

        return response()->json([
            'success' => true,
            'data' => $networkData,
            'level' => $level
        ]);
    }

    private function getNetworkStatistics($user)
    {
        $level1 = User::where('sponsor_id', $user->id)->count();
        $level2 = User::whereIn('sponsor_id', User::where('sponsor_id', $user->id)->pluck('id'))->count();
        $level3 = User::whereIn('sponsor_id',
            User::whereIn('sponsor_id', User::where('sponsor_id', $user->id)->pluck('id'))->pluck('id')
        )->count();

        $totalEarnings = User::whereIn('id', function($query) use ($user) {
            $query->select('user_id')
                  ->from('earnings')
                  ->whereIn('user_id', function($subQuery) use ($user) {
                      $subQuery->select('id')
                               ->from('users')
                               ->where('sponsor_id', $user->id)
                               ->orWhereIn('sponsor_id', function($deepQuery) use ($user) {
                                   $deepQuery->select('id')
                                            ->from('users')
                                            ->where('sponsor_id', $user->id);
                               });
                  });
        })->with('earnings')->get()->sum(function($user) {
            return $user->earnings->sum('amount');
        });

        return [
            'level1' => $level1,
            'level2' => $level2,
            'level3' => $level3,
            'total' => $level1 + $level2 + $level3,
            'total_earnings' => $totalEarnings
        ];
    }

    private function buildNetworkTree($user, $depth = 0, $maxDepth = 3)
    {
        if ($depth >= $maxDepth) {
            return null;
        }

        $children = User::where('sponsor_id', $user->id)
            ->with('earnings')
            ->get()
            ->map(function($child) use ($depth, $maxDepth) {
                return $this->buildNetworkTree($child, $depth + 1, $maxDepth);
            })
            ->filter()
            ->values();

        $totalEarnings = $user->earnings->sum('amount');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'level' => $depth + 1,
            'earnings' => $totalEarnings,
            'children' => $children,
            'created_at' => $user->created_at->format('M d, Y')
        ];
    }

    private function getNetworkByLevel($user, $level, $maxLevel)
    {
        $users = collect([$user]);

        for ($i = 1; $i <= $level; $i++) {
            $userIds = $users->pluck('id');
            $users = User::whereIn('sponsor_id', $userIds)->get();
        }

        return $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'earnings' => $user->earnings->sum('amount'),
                'created_at' => $user->created_at->format('M d, Y')
            ];
        });
    }
}