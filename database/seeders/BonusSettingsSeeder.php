<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BonusSettings;

class BonusSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BonusSettings::firstOrCreate([], [
            'package_value' => 1200.00,
            'direct_bonus_percent' => 100.00,
            'pair_bonus_amount' => 240.00,
            'balancer_ratio' => '1:1',
            'matching_bonus_percent' => 20.00,
        ]);
    }
}
