<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BonusRule;

class BonusRulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bonusRules = [
            [
                'name' => 'Direct Referral Bonus',
                'type' => 'direct_referral',
                'percentage' => 10.00,
                'min_amount' => 500.00,
                'max_amount' => 5000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Level 1 Binary Bonus',
                'type' => 'level_bonus',
                'percentage' => 5.00,
                'min_amount' => 200.00,
                'max_amount' => 2000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Level 2 Binary Bonus',
                'type' => 'level_bonus',
                'percentage' => 3.00,
                'min_amount' => 100.00,
                'max_amount' => 1000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Level 3 Binary Bonus',
                'type' => 'level_bonus',
                'percentage' => 2.00,
                'min_amount' => 50.00,
                'max_amount' => 500.00,
                'is_active' => true,
            ],
            [
                'name' => 'Matching Bonus',
                'type' => 'matching_bonus',
                'percentage' => 8.00,
                'min_amount' => 300.00,
                'max_amount' => 3000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Leadership Bonus',
                'type' => 'leadership_bonus',
                'percentage' => 15.00,
                'min_amount' => 1000.00,
                'max_amount' => 10000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Performance Bonus',
                'type' => 'direct_referral',
                'percentage' => 12.00,
                'min_amount' => 800.00,
                'max_amount' => 8000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Team Building Bonus',
                'type' => 'level_bonus',
                'percentage' => 4.00,
                'min_amount' => 150.00,
                'max_amount' => 1500.00,
                'is_active' => true,
            ],
        ];

        foreach ($bonusRules as $rule) {
            BonusRule::create($rule);
        }
    }
}