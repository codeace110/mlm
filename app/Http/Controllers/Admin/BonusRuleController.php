<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BonusRule;
use Illuminate\Http\Request;

class BonusRuleController extends Controller
{
    public function index()
    {
        $rules = BonusRule::all();
        return view('admin.bonus_rules.index', compact('rules'));
    }

    public function activate(BonusRule $rule)
    {
        $rule->update(['active' => true]);
        return back()->with('success', 'Bonus rule activated!');
    }

    public function deactivate(BonusRule $rule)
    {
        $rule->update(['active' => false]);
        return back()->with('error', 'Bonus rule deactivated!');
    }
}
