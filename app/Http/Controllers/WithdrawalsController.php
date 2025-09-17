<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Withdrawal;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Services\NotificationService;

class WithdrawalsController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:500|max:' . Auth::user()->account_balance,
            'method' => 'required|in:cebuana_lhuillier,mlhuillier,palawan_pawnshop,gcash,paymaya',
            'account_details' => 'required|array',
        ]);

        $user = Auth::user();

        // Check balance
        if ($user->account_balance < $request->amount) {
            return redirect()->back()->with('error', 'Insufficient balance.');
        }

        // Check required profile data
        if (empty($user->phone) || empty($user->address)) {
            return redirect()->back()->with('error', 'Please complete your profile (phone and address) before requesting a withdrawal.');
        }

        // Create withdrawal request (balance will be deducted on approval)
        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'method' => $request->method,
            'account_details' => $request->account_details,
            'status' => 'pending',
        ]);

        // Create notification for the user
        $notificationService = new NotificationService();
        $notificationService->createNotification(
            $user->id,
            'warning',
            'Withdrawal Request Submitted',
            "Your withdrawal request for ₱" . number_format($request->amount, 2) . " via {$request->method} has been submitted and is pending approval.",
            'money-bill-wave',
            'warning',
            [
                'withdrawal_id' => $withdrawal->id,
                'amount' => $request->amount,
                'method' => $request->method
            ]
        );

        return redirect()->route('dashboard.payout')->with('success', 'Withdrawal request submitted successfully! Balance will be deducted upon admin approval.');
    }

    public function ajaxStats(): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->getWithdrawalStats($user);

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    public function ajaxRecent(Request $request): JsonResponse
    {
        $user = Auth::user();
        $limit = $request->get('limit', 5);

        $recentWithdrawals = Withdrawal::where('user_id', $user->id)
            ->latest()
            ->take($limit)
            ->get()
            ->map(function($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'method' => $withdrawal->method,
                    'status' => $withdrawal->status,
                    'created_at' => $withdrawal->created_at->format('M d, Y H:i')
                ];
            });

        return response()->json([
            'success' => true,
            'withdrawals' => $recentWithdrawals
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();

        $query = Withdrawal::where('user_id', $user->id);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by method
        if ($request->has('method') && $request->method !== '') {
            $query->where('method', $request->method);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $withdrawals = $query->latest()->paginate(15);

        // Get withdrawal statistics
        $stats = $this->getWithdrawalStats($user);

        // Payment methods configuration
        $paymentMethods = [
            [
                'name' => 'Cebuana Lhuillier',
                'processing_time' => '1-2 business days',
                'fee' => 30,
                'color' => 'primary'
            ],
            [
                'name' => 'M Lhuillier',
                'processing_time' => '1-2 business days',
                'fee' => 30,
                'color' => 'success'
            ],
            [
                'name' => 'Palawan Pawnshop',
                'processing_time' => '1-2 business days',
                'fee' => 30,
                'color' => 'info'
            ],
            [
                'name' => 'GCash',
                'processing_time' => 'Same day',
                'fee' => 15,
                'color' => 'warning'
            ],
            [
                'name' => 'PayMaya',
                'processing_time' => 'Same day',
                'fee' => 15,
                'color' => 'warning'
            ]
        ];

        return view('dashboard-payout', compact('withdrawals', 'stats', 'user', 'paymentMethods'));
    }

    private function getWithdrawalStats($user)
    {
        $totalWithdrawals = Withdrawal::where('user_id', $user->id)->sum('amount');
        $pendingWithdrawals = Withdrawal::where('user_id', $user->id)->where('status', 'pending')->sum('amount');
        $approvedWithdrawals = Withdrawal::where('user_id', $user->id)->where('status', 'approved')->sum('amount');
        $deniedWithdrawals = Withdrawal::where('user_id', $user->id)->where('status', 'denied')->count();

        $monthlyWithdrawals = Withdrawal::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        return [
            'total' => $totalWithdrawals,
            'pending' => $pendingWithdrawals,
            'approved' => $approvedWithdrawals,
            'denied_count' => $deniedWithdrawals,
            'monthly' => $monthlyWithdrawals,
            'available_balance' => $user->account_balance
        ];
    }
}