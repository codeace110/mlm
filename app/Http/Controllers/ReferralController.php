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

    public function codes()
    {
        $user = Auth::user();

        // Get user's assigned referral codes
        $referralCodes = $user->referralCodes()->with('usedBy')->paginate(20);

        // Stats
        $totalCodes = $user->referralCodes()->count();
        $assignedCodes = $user->referralCodes()->whereNotNull('assigned_to')->count();
        $usedCodes = $user->referralCodes()->whereNotNull('used_by')->count();

        return view('dashboard.referral-codes', compact('referralCodes', 'totalCodes', 'assignedCodes', 'usedCodes'));
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


}