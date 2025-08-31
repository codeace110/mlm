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
        $rule->update(['is_active' => true]);
        return back()->with('success', 'Bonus rule activated!');
    }

    public function deactivate(BonusRule $rule)
    {
        $rule->update(['is_active' => false]);
        return back()->with('error', 'Bonus rule deactivated!');
    }

    public function create()
    {
        return view('admin.bonus_rules.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'percentage' => 'required|numeric|min:0|max:100',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        BonusRule::create($request->all());
        return redirect()->route('admin.bonus_rules.index')->with('success', 'Bonus rule created successfully!');
    }

    public function edit(BonusRule $bonusRule)
    {
        return view('admin.bonus_rules.edit', compact('bonusRule'));
    }

    public function update(Request $request, BonusRule $bonusRule)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'percentage' => 'required|numeric|min:0|max:100',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $bonusRule->update($request->all());
        return redirect()->route('admin.bonus_rules.index')->with('success', 'Bonus rule updated successfully!');
    }

    public function destroy(BonusRule $bonusRule)
    {
        $bonusRule->delete();
        return redirect()->route('admin.bonus_rules.index')->with('success', 'Bonus rule deleted successfully!');
    }
}
