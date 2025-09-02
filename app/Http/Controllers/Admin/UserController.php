<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PackagePurchase;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::count();
        $activePlans = \App\Models\Package::where('is_active', true)->count();
        $pendingWithdrawals = \App\Models\Withdrawal::where('status', 'pending')->count();

        return view('admin.dashboard', compact('totalUsers', 'activePlans', 'pendingWithdrawals'));
    }

    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function approve(User $user)
    {
        $user->update(['status' => 'approved']);
        return back()->with('success', 'User approved!');
    }

    public function deny(User $user)
    {
        $user->update(['status' => 'denied']);
        return back()->with('error', 'User denied!');
    }

    public function show(User $user)
    {
        $packagePurchases = PackagePurchase::where('user_id', $user->id)
            ->with('package')
            ->latest()
            ->get();

        return view('admin.users.show', compact('user', 'packagePurchases'));
    }

    public function approvePackagePurchase(PackagePurchase $packagePurchase)
    {
        // Check if user has sufficient balance
        $user = $packagePurchase->user;
        if ($user->account_balance < $packagePurchase->total_amount) {
            return back()->with('error', 'User has insufficient balance for this purchase.');
        }

        // Deduct balance and approve purchase
        $user->decrement('account_balance', $packagePurchase->total_amount);

        $packagePurchase->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'admin_notes' => 'Payment approved and processed successfully.'
        ]);

        return back()->with('success', 'Package purchase approved and payment processed!');
    }

    public function denyPackagePurchase(Request $request, PackagePurchase $packagePurchase)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:500'
        ]);

        $packagePurchase->update([
            'status' => 'denied',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'admin_notes' => $request->admin_notes
        ]);

        return back()->with('success', 'Package purchase denied.');
    }
}
