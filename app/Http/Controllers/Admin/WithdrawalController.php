<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;

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
        return back()->with('success', 'Withdrawal approved and balance deducted!');
    }

    public function deny(Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'This withdrawal has already been processed.');
        }

        $withdrawal->update(['status' => 'denied']);
        return back()->with('error', 'Withdrawal denied!');
    }
}
