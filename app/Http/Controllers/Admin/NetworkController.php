<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class NetworkController extends Controller
{
    public function index()
    {
        // later you can load a binary tree of users
        return view('admin.network.index');
    }
}
