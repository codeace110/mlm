<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Earning;

class EarningController extends Controller
{
    public function index()
    {
        $earnings = Earning::with('user')->latest()->paginate(20);
        return view('admin.earnings.index', compact('earnings'));
    }
}
