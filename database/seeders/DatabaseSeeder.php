<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\BonusSettings;
use App\Models\BonusRule;
use App\Models\AdminCode;
use App\Models\BinaryTree;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@mlm.com',
            'password' => Hash::make('admin123'),
            'phone' => '1234567890',
            'address' => 'System Admin Address',
            'city' => 'Admin City',
            'province' => 'Admin Province',
            'shipping_name' => 'System Administrator',
            'is_admin' => true,
            'status' => 'approved',
            'account_balance' => 0.00,
            'level' => 1,
            'balancing_mode' => '1:1',
            'email_verified_at' => now(),
        ]);

        // Create binary tree record for admin
        BinaryTree::create([
            'user_id' => $admin->id,
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'left_spillover' => 0,
            'right_spillover' => 0,
        ]);

        // Create bonus settings
        BonusSettings::create([
            'package_value' => 1200.00,
            'direct_bonus_percent' => 100.00, // ₱100 direct bonus
            'pair_bonus_amount' => 100.00, // ₱100 per pair
            'balancer_ratio' => '1:1',
            'matching_bonus_percent' => 20.00,
        ]);

        // Create bonus rules
        $bonusRules = [
            [
                'name' => 'Direct Referral Bonus',
                'type' => 'direct',
                'percentage' => 100.00,
                'min_amount' => 100.00,
                'max_amount' => 100.00,
                'is_active' => true,
            ],
            [
                'name' => 'Level 1 Pair Matching',
                'type' => 'pair',
                'percentage' => 100.00,
                'min_amount' => 100.00,
                'max_amount' => 100.00,
                'is_active' => true,
            ],
            [
                'name' => 'Level 2 Pair Matching',
                'type' => 'pair',
                'percentage' => 100.00,
                'min_amount' => 100.00,
                'max_amount' => 100.00,
                'is_active' => true,
            ],
            [
                'name' => 'Level 3 Pair Matching',
                'type' => 'pair',
                'percentage' => 100.00,
                'min_amount' => 100.00,
                'max_amount' => 100.00,
                'is_active' => true,
            ],
            [
                'name' => 'Level 4 Pair Matching',
                'type' => 'pair',
                'percentage' => 100.00,
                'min_amount' => 100.00,
                'max_amount' => 100.00,
                'is_active' => true,
            ],
            [
                'name' => 'Level 5 Pair Matching',
                'type' => 'pair',
                'percentage' => 100.00,
                'min_amount' => 100.00,
                'max_amount' => 100.00,
                'is_active' => true,
            ],
            [
                'name' => 'Product Reward (Every 5th Pair)',
                'type' => 'product',
                'percentage' => 0.00,
                'min_amount' => 0.00,
                'max_amount' => 0.00,
                'is_active' => true,
            ],
        ];

        foreach ($bonusRules as $rule) {
            BonusRule::create($rule);
        }

        // Create a sample distributor user
        $distributor = User::create([
            'name' => 'Sample Distributor',
            'email' => 'distributor@mlm.com',
            'password' => Hash::make('distributor123'),
            'phone' => '0987654321',
            'address' => 'Distributor Address',
            'city' => 'Distributor City',
            'province' => 'Distributor Province',
            'shipping_name' => 'Sample Distributor',
            'is_admin' => false,
            'status' => 'approved',
            'account_balance' => 0.00,
            'level' => 1,
            'balancing_mode' => '1:1',
            'email_verified_at' => now(),
        ]);

        // Create binary tree record for distributor
        BinaryTree::create([
            'user_id' => $distributor->id,
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'left_spillover' => 0,
            'right_spillover' => 0,
        ]);

        // Generate UUID-based referral codes (available for admin to assign)
        $uuidService = new \App\Services\EnhancedReferralCodeService();
        $uuidCodes = $uuidService->generateBatch($admin, 5, 'Initial UUID Batch', 30);

        // Codes remain available (status: available) for admin to assign to distributors

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin login: admin@mlm.com / admin123');
        $this->command->info('Distributor login: distributor@mlm.com / distributor123');
    }
}