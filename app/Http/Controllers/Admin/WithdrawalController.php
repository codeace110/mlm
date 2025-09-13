<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\NotificationService;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::with('user')->latest()->paginate(20);
        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function approve(Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'This withdrawal has already been processed.');
        }

        // Check if user has sufficient balance
        if ($withdrawal->user->account_balance < $withdrawal->amount) {
            return back()->with('error', 'User has insufficient balance for this withdrawal.');
        }

        // Deduct balance
        $withdrawal->user->decrement('account_balance', $withdrawal->amount);

        $withdrawal->update(['status' => 'approved']);

        // Create notification for user
        $notificationService = new NotificationService();
        $notificationService->createNotification(
            $withdrawal->user_id,
            'success',
            'Withdrawal Approved',
            "Your withdrawal request for ₱" . number_format($withdrawal->amount, 2) . " via {$withdrawal->method} has been approved. Funds will be processed shortly.",
            'check-circle',
            'success',
            [
                'withdrawal_id' => $withdrawal->id,
                'amount' => $withdrawal->amount,
                'method' => $withdrawal->method,
                'status' => 'approved'
            ]
        );

        return back()->with('success', 'Withdrawal approved and balance deducted!');
    }

    public function deny(Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'This withdrawal has already been processed.');
        }

        $withdrawal->update(['status' => 'denied']);

        // Create notification for user
        $notificationService = new NotificationService();
        $notificationService->createNotification(
            $withdrawal->user_id,
            'danger',
            'Withdrawal Denied',
            "Your withdrawal request for ₱" . number_format($withdrawal->amount, 2) . " via {$withdrawal->method} has been denied. Please contact support for more information.",
            'times-circle',
            'danger',
            [
                'withdrawal_id' => $withdrawal->id,
                'amount' => $withdrawal->amount,
                'method' => $withdrawal->method,
                'status' => 'denied'
            ]
        );

        return back()->with('error', 'Withdrawal denied!');
    }
}
