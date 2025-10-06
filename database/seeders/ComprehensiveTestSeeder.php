<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AdminCode;
use App\Models\BinaryTree;
use App\Models\Bonus;
use App\Models\Earning;
use App\Models\Withdrawal;
use App\Services\BinaryBalancerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ComprehensiveTestSeeder extends Seeder
{
    /**
     * Run the database seeds for comprehensive testing
     */
    public function run(): void
    {
        $this->command->info('Creating comprehensive test data for MLM system...');

        // Create admin user
        $admin = $this->createAdminUser();

        // Create test distributors with different scenarios
        $distributors = $this->createTestDistributors();

        // Create network structures for testing
        $this->createNetworkStructures($distributors);

        // Create admin codes for testing
        $this->createAdminCodes($admin, $distributors);

        // Create bonus scenarios for testing
        $this->createBonusScenarios($distributors);

        // Create earnings and withdrawal data
        $this->createEarningsAndWithdrawals($distributors);

        // Create edge cases for testing
        $this->createEdgeCases();

        $this->command->info('Comprehensive test data created successfully!');
        $this->command->info("Admin: {$admin->email} / password: admin123");
        $this->command->info("Created " . count($distributors) . " test distributors");
        $this->command->info("Created " . AdminCode::count() . " admin codes");
        $this->command->info("Created " . Bonus::count() . " bonuses");
    }

    /**
     * Create admin user
     */
    private function createAdminUser(): User
    {
        $admin = User::create([
            'id' => 'ADMIN001',
            'name' => 'System Administrator',
            'email' => 'admin@test.com',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
            'status' => 'active',
            'balancing_mode' => '1:1',
        ]);

        BinaryTree::create([
            'user_id' => $admin->id,
            'parent_id' => null,
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 1,
            'reward_count' => 0,
            'direct_pairs_paid' => 0,
        ]);

        return $admin;
    }

    /**
     * Create test distributors with different scenarios
     */
    private function createTestDistributors(): array
    {
        $distributors = [];

        // Distributor 1: Active with balanced network
        $dist1 = User::create([
            'id' => 'DIST001',
            'name' => 'Balanced Distributor',
            'email' => 'balanced@test.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'status' => 'active',
            'balancing_mode' => '1:1',
        ]);

        BinaryTree::create([
            'user_id' => $dist1->id,
            'parent_id' => null,
            'total_left_volume' => 50,
            'total_right_volume' => 50,
            'left_consumed' => 8,
            'right_consumed' => 8,
            'level_index' => 3,
            'reward_count' => 2,
            'direct_pairs_paid' => 1,
        ]);

        $distributors[] = $dist1;

        // Distributor 2: Left-heavy network
        $dist2 = User::create([
            'id' => 'DIST002',
            'name' => 'Left Heavy Distributor',
            'email' => 'leftheavy@test.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'status' => 'active',
            'balancing_mode' => '1:1',
        ]);

        BinaryTree::create([
            'user_id' => $dist2->id,
            'total_left_volume' => 100,
            'total_right_volume' => 10,
            'level_index' => 4,
            'left_consumed' => 16,
            'right_consumed' => 0,
            'reward_count' => 4,
        ]);

        $distributors[] = $dist2;

        // Distributor 3: Right-heavy network
        $dist3 = User::create([
            'id' => 'DIST003',
            'name' => 'Right Heavy Distributor',
            'email' => 'rightheavy@test.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'status' => 'active',
            'balancing_mode' => '1:1',
        ]);

        BinaryTree::create([
            'user_id' => $dist3->id,
            'total_left_volume' => 20,
            'total_right_volume' => 200,
            'level_index' => 5,
            'left_consumed' => 0,
            'right_consumed' => 32,
            'reward_count' => 6,
        ]);

        $distributors[] = $dist3;

        // Distributor 4: High reward count (for product rewards)
        $dist4 = User::create([
            'id' => 'DIST004',
            'name' => 'High Reward Distributor',
            'email' => 'highreward@test.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'status' => 'active',
            'balancing_mode' => '1:1',
        ]);

        BinaryTree::create([
            'user_id' => $dist4->id,
            'total_left_volume' => 30,
            'total_right_volume' => 30,
            'level_index' => 2,
            'left_consumed' => 4,
            'right_consumed' => 4,
            'reward_count' => 4, // Next reward will be 5th (product)
        ]);

        $distributors[] = $dist4;

        // Distributor 5: New distributor with no network
        $dist5 = User::create([
            'id' => 'DIST005',
            'name' => 'New Distributor',
            'email' => 'newdist@test.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'status' => 'active',
            'balancing_mode' => '1:1',
        ]);

        BinaryTree::create([
            'user_id' => $dist5->id,
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'level_index' => 1,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'reward_count' => 0,
        ]);

        $distributors[] = $dist5;

        return $distributors;
    }

    /**
     * Create network structures for testing
     */
    private function createNetworkStructures(array $distributors): void
    {
        foreach ($distributors as $distributor) {
            // Create 2-4 direct referrals for each distributor
            $directCount = rand(2, 4);

            for ($i = 1; $i <= $directCount; $i++) {
                $referral = User::create([
                    'id' => $distributor->id . 'REF' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'name' => 'Referral ' . $i . ' of ' . $distributor->name,
                    'email' => 'ref' . $i . '_' . $distributor->id . '@test.com',
                    'password' => Hash::make('password123'),
                    'is_admin' => false,
                    'status' => 'active',
                    'sponsor_id' => $distributor->id,
                    'placement_side' => $i % 2 === 1 ? 'left' : 'right',
                    'balancing_mode' => $distributor->balancing_mode,
                ]);

                BinaryTree::create([
                    'user_id' => $referral->id,
                    'parent_id' => $distributor->id,
                    'total_left_volume' => rand(10, 25),
                    'total_right_volume' => rand(10, 25),
                    'left_consumed' => rand(0, 5),
                    'right_consumed' => rand(0, 5),
                    'level_index' => rand(2, 4),
                    'reward_count' => rand(0, 3),
                    'direct_pairs_paid' => rand(0, 2),
                ]);

                // Create 1-2 levels deep for some referrals
                if (rand(1, 3) === 1) {
                    $this->createDownline($referral, 2);
                }
            }
        }
    }

    /**
     * Create downline for a user
     */
    private function createDownline(User $sponsor, int $depth): void
    {
        if ($depth <= 0) return;

        for ($i = 1; $i <= 2; $i++) {
            $downline = User::create([
                'id' => $sponsor->id . 'DL' . $i,
                'name' => 'Downline ' . $i . ' of ' . $sponsor->name,
                'email' => 'dl' . $i . '_' . $sponsor->id . '@test.com',
                'password' => Hash::make('password123'),
                'is_admin' => false,
                'status' => 'active',
                'sponsor_id' => $sponsor->id,
                'placement_side' => $i === 1 ? 'left' : 'right',
                'balancing_mode' => $sponsor->balancing_mode,
            ]);

            BinaryTree::create([
                'user_id' => $downline->id,
                'parent_id' => $sponsor->id,
                'total_left_volume' => rand(5, 15),
                'total_right_volume' => rand(5, 15),
                'left_consumed' => rand(0, 3),
                'right_consumed' => rand(0, 3),
                'level_index' => rand(3, 5),
                'reward_count' => rand(0, 2),
                'direct_pairs_paid' => rand(0, 1),
            ]);

            $this->createDownline($downline, $depth - 1);
        }
    }

    /**
     * Create admin codes for testing
     */
    private function createAdminCodes(User $admin, array $distributors): void
    {
        // Create issued codes (available for assignment)
        for ($i = 1; $i <= 20; $i++) {
            AdminCode::create([
                'code' => 'ISSUED' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'tracker' => 'TRACKER' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'batch_id' => 'BATCH001',
                'status' => 'issued',
                'issued_to_user_id' => null,
                'issued_by_admin_id' => $admin->id,
                'issued_at' => now(),
                'notes' => 'Test issued code ' . $i,
            ]);
        }

        // Create unused codes (assigned to distributors)
        foreach ($distributors as $distributor) {
            for ($i = 1; $i <= 3; $i++) {
                AdminCode::create([
                    'code' => $distributor->id . 'CODE' . $i,
                    'tracker' => $distributor->id . 'TRACK' . $i,
                    'batch_id' => 'BATCH002',
                    'status' => 'unused',
                    'issued_to_user_id' => $distributor->id,
                    'issued_by_admin_id' => $admin->id,
                    'issued_at' => now()->subDays(rand(1, 30)),
                    'notes' => 'Assigned to distributor ' . $distributor->name,
                ]);
            }
        }

        // Create used codes (for testing history)
        for ($i = 1; $i <= 10; $i++) {
            $usedByUser = User::factory()->create();
            $distributor = $distributors[array_rand($distributors)];
            AdminCode::create([
                'code' => 'USED' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'tracker' => 'USEDTRACK' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'batch_id' => 'BATCH003',
                'status' => 'used',
                'issued_to_user_id' => $distributor->id,
                'issued_by_admin_id' => $admin->id,
                'issued_at' => now()->subDays(rand(30, 60)),
                'used_by_user_id' => $usedByUser->id,
                'used_at' => now()->subDays(rand(1, 30)),
                'notes' => 'Used by ' . $usedByUser->name,
            ]);
        }
    }

    /**
     * Create bonus scenarios for testing
     */
    private function createBonusScenarios(array $distributors): void
    {
        foreach ($distributors as $distributor) {
            // Create direct bonuses
            $directPairs = rand(1, 5);
            for ($i = 1; $i <= $directPairs; $i++) {
                $isProduct = ($i % 5 === 0); // Every 5th is product
                Bonus::create([
                    'user_id' => $distributor->id,
                    'amount' => $isProduct ? 0 : 100,
                    'is_product' => $isProduct,
                    'reward_type' => 'direct',
                    'pair_count' => 1,
                    'description' => $isProduct
                        ? "Direct referral product reward for {$i}th pair"
                        : "Direct referral bonus ₱100 for {$i}th pair",
                    'status' => 'pending',
                ]);
            }

            // Create level bonuses
            $levelCount = rand(1, 3);
            for ($i = 1; $i <= $levelCount; $i++) {
                $levelIndex = rand(1, 4);
                $isProduct = (($i + 4) % 5 === 0); // Different pattern for level bonuses
                Bonus::create([
                    'user_id' => $distributor->id,
                    'amount' => $isProduct ? 0 : 100,
                    'is_product' => $isProduct,
                    'reward_type' => 'level',
                    'level_index' => $levelIndex,
                    'pair_count' => 1,
                    'description' => $isProduct
                        ? "Level {$levelIndex} product reward"
                        : "Level {$levelIndex} bonus ₱100",
                    'status' => 'pending',
                ]);
            }
        }
    }

    /**
     * Create earnings and withdrawals data
     */
    private function createEarningsAndWithdrawals(array $distributors): void
    {
        foreach ($distributors as $distributor) {
            // Create earnings
            $earningsCount = rand(5, 15);
            for ($i = 1; $i <= $earningsCount; $i++) {
                Earning::create([
                    'user_id' => $distributor->id,
                    'amount' => rand(50, 200),
                    'type' => rand(0, 1) ? 'direct_bonus' : 'level_bonus',
                    'description' => 'Test earning ' . $i,
                    'status' => 'completed',
                    'created_at' => now()->subDays(rand(1, 60)),
                ]);
            }

            // Create some withdrawals
            $withdrawalCount = rand(0, 3);
            for ($i = 1; $i <= $withdrawalCount; $i++) {
                Withdrawal::create([
                    'user_id' => $distributor->id,
                    'amount' => rand(100, 500),
                    'status' => rand(0, 1) ? 'pending' : 'completed',
                    'payment_method' => 'bank_transfer',
                    'account_details' => 'Test account details',
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            }

            // Update account balance
            $totalEarnings = Earning::where('user_id', $distributor->id)->sum('amount');
            $totalWithdrawals = Withdrawal::where('user_id', $distributor->id)
                ->where('status', 'completed')
                ->sum('amount');
            $distributor->update(['account_balance' => $totalEarnings - $totalWithdrawals]);
        }
    }

    /**
     * Create edge cases for testing
     */
    private function createEdgeCases(): void
    {
        // Create user with maximum depth network
        $deepNetworkRoot = User::create([
            'id' => 'DEEP001',
            'name' => 'Deep Network Root',
            'email' => 'deep@test.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'status' => 'active',
            'balancing_mode' => '1:1',
        ]);

        $this->createDeepNetwork($deepNetworkRoot, 5);

        // Create user with very high volume
        $highVolumeUser = User::create([
            'id' => 'HIGHVOL1',
            'name' => 'High Volume User',
            'email' => 'highvol@test.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'status' => 'active',
            'balancing_mode' => '1:1',
        ]);

        BinaryTree::create([
            'user_id' => $highVolumeUser->id,
            'parent_id' => null,
            'total_left_volume' => 1000,
            'total_right_volume' => 1000,
            'left_consumed' => 128,
            'right_consumed' => 128,
            'level_index' => 8,
            'reward_count' => 15,
            'direct_pairs_paid' => 8,
        ]);

        // Create admin codes with special characters
        AdminCode::create([
            'code' => 'TEST-001',
            'tracker' => 'TEST-TRACKER-001',
            'batch_id' => 'SPECIAL001',
            'status' => 'issued',
            'issued_to_user_id' => null,
            'issued_by_admin_id' => $admin->id,
            'issued_at' => now(),
            'notes' => 'Special characters test code',
        ]);

        AdminCode::create([
            'code' => 'TEST_002',
            'tracker' => 'TEST_TRACKER_002',
            'batch_id' => 'SPECIAL001',
            'status' => 'issued',
            'issued_to_user_id' => null,
            'issued_by_admin_id' => $admin->id,
            'issued_at' => now(),
            'notes' => 'Special characters test code',
        ]);
    }

    /**
     * Create deep network structure
     */
    private function createDeepNetwork(User $root, int $depth): void
    {
        if ($depth <= 0) return;

        $leftChild = User::create([
            'id' => $root->id . 'L',
            'name' => 'Left Child of ' . $root->name,
            'email' => 'left_' . $root->id . '@test.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'status' => 'active',
            'sponsor_id' => $root->id,
            'placement_side' => 'left',
            'balancing_mode' => '1:1',
        ]);

        $rightChild = User::create([
            'id' => $root->id . 'R',
            'name' => 'Right Child of ' . $root->name,
            'email' => 'right_' . $root->id . '@test.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'status' => 'active',
            'sponsor_id' => $root->id,
            'placement_side' => 'right',
            'balancing_mode' => '1:1',
        ]);

        BinaryTree::create([
            'user_id' => $leftChild->id,
            'parent_id' => $root->id,
            'total_left_volume' => 1,
            'total_right_volume' => 1,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 2,
            'reward_count' => 0,
            'direct_pairs_paid' => 0,
        ]);

        BinaryTree::create([
            'user_id' => $rightChild->id,
            'parent_id' => $root->id,
            'total_left_volume' => 1,
            'total_right_volume' => 1,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 2,
            'reward_count' => 0,
            'direct_pairs_paid' => 0,
        ]);

        $this->createDeepNetwork($leftChild, $depth - 1);
        $this->createDeepNetwork($rightChild, $depth - 1);
    }
}