<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Earning;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class EarningsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Earning::where('user_id', $user->id);

        // Filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $earnings = $query->latest()->paginate(15);

        // Get earnings statistics
        $stats = $this->getEarningsStats($user);

        // Get earnings by type
        $earningsByType = Earning::where('user_id', $user->id)
            ->selectRaw('type, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        return view('earnings', compact('earnings', 'stats', 'earningsByType'));
    }

    public function ajaxStats(): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->getEarningsStats($user);

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    public function ajaxRecent(Request $request): JsonResponse
    {
        $user = Auth::user();
        $limit = $request->get('limit', 5);

        $recentEarnings = Earning::where('user_id', $user->id)
            ->latest()
            ->take($limit)
            ->get()
            ->map(function($earning) {
                return [
                    'id' => $earning->id,
                    'amount' => $earning->amount,
                    'type' => $earning->type,
                    'description' => $earning->description,
                    'status' => $earning->status,
                    'created_at' => $earning->created_at->format('M d, Y H:i')
                ];
            });

        return response()->json([
            'success' => true,
            'earnings' => $recentEarnings
        ]);
    }

    private function getEarningsStats($user)
    {
        $totalEarnings = Earning::where('user_id', $user->id)->sum('amount');
        $pendingEarnings = Earning::where('user_id', $user->id)->where('status', 'pending')->sum('amount');
        $completedEarnings = Earning::where('user_id', $user->id)->where('status', 'completed')->sum('amount');

        $monthlyEarnings = Earning::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $weeklyEarnings = Earning::where('user_id', $user->id)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount');

        $todayEarnings = Earning::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->sum('amount');

        return [
            'total' => $totalEarnings,
            'pending' => $pendingEarnings,
            'completed' => $completedEarnings,
            'monthly' => $monthlyEarnings,
            'weekly' => $weeklyEarnings,
            'today' => $todayEarnings
        ];
    }
}