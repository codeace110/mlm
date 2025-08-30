<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
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
}
