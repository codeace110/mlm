<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Earning;
use App\Models\User;

class EarningsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('is_admin', false)->get();
        $earningTypes = [
            'direct_referral',
            'level_bonus',
            'matching_bonus',
            'leadership_bonus',
            'performance_bonus'
        ];

        $descriptions = [
            'direct_referral' => 'Direct referral bonus from new member signup',
            'level_bonus' => 'Level bonus from downline activity',
            'matching_bonus' => 'Matching bonus from team performance',
            'leadership_bonus' => 'Leadership bonus for team building',
            'performance_bonus' => 'Performance bonus for achieving targets'
        ];

        foreach ($users as $user) {
            // Create 3-8 random earnings per user
            $numEarnings = rand(3, 8);

            for ($i = 0; $i < $numEarnings; $i++) {
                $type = $earningTypes[array_rand($earningTypes)];
                $amount = rand(100, 5000); // Random amount between 100 and 5000

                Earning::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'type' => $type,
                    'description' => $descriptions[$type],
                    'status' => 'completed',
                    'created_at' => now()->subDays(rand(1, 90)), // Random date within last 90 days
                ]);
            }
        }

        // Create some pending earnings
        $pendingUsers = $users->take(10); // First 10 users
        foreach ($pendingUsers as $user) {
            Earning::create([
                'user_id' => $user->id,
                'amount' => rand(500, 2000),
                'type' => 'direct_referral',
                'description' => 'Pending direct referral bonus',
                'status' => 'pending',
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }
    }
}