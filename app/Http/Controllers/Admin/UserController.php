<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

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
        return view('admin.users.show', compact('user'));
    }
}
