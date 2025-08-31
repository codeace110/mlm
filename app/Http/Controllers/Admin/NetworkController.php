<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class NetworkController extends Controller
{
    public function index()
    {
        $network = \App\Models\User::with('sponsor')->get()->map(function ($user) {
            $user->level = $this->calculateLevel($user);
            return $user;
        });
        return view('admin.network.index', compact('network'));
    }

    private function calculateLevel($user, $level = 0)
    {
        if (!$user->sponsor) {
            return $level;
        }
        return $this->calculateLevel($user->sponsor, $level + 1);
    }
}
