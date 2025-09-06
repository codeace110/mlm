<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::count();
        $pendingWithdrawals = \App\Models\Withdrawal::where('status', 'pending')->count();

        return view('admin.dashboard', compact('totalUsers', 'pendingWithdrawals'));
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
        return view('admin.users.show', compact('user'));
    }

    public function generateReferralCode(User $user)
    {
        $code = \App\Models\ReferralCode::create([
            'code' => strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)),
            'assigned_to' => $user->id,
            'generated_by' => auth()->id(),
            'status' => 'available',
            'expires_at' => now()->addDays(30),
        ]);

        return back()->with('success', 'Referral code generated: ' . $code->code);
    }

}
