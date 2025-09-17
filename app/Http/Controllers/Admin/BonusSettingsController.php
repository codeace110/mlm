<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BonusSettings;
use Illuminate\Http\Request;

class BonusSettingsController extends Controller
{
    /**
     * Display the bonus settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = BonusSettings::first();
        return view('admin.bonus_settings.index', compact('settings'));
    }

    /**
     * Update the bonus settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'package_value' => 'required|numeric|min:0',
            'direct_bonus_percent' => 'required|numeric|min:0|max:100',
            'pair_bonus_amount' => 'required|numeric|min:0',
            'balancer_ratio' => 'required|in:1:1,2:1,3:1',
            'matching_bonus_percent' => 'required|numeric|min:0|max:100',
        ]);

        $settings = BonusSettings::first();
        if (!$settings) {
            $settings = new BonusSettings();
        }

        $settings->update($request->only([
            'package_value',
            'direct_bonus_percent',
            'pair_bonus_amount',
            'balancer_ratio',
            'matching_bonus_percent',
        ]));

        return redirect()->route('admin.bonus_settings.index')
            ->with('success', 'Bonus settings updated successfully.');
    }
}
