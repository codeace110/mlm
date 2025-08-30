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
        $withdrawal->update(['status' => 'approved']);
        return back()->with('success', 'Withdrawal approved!');
    }

    public function deny(Withdrawal $withdrawal)
    {
        $withdrawal->update(['status' => 'denied']);
        return back()->with('error', 'Withdrawal denied!');
    }
}
