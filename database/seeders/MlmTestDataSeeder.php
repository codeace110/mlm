<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\BinaryTree;
use App\Models\ReferralCode;
use App\Models\Bonus;
use App\Services\ReferralCodeService;
use App\Services\BinaryTreePlacementService;
use App\Services\BinaryBalancerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MlmTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating MLM test data...');

        // Create admin user
        $admin = $this->createAdminUser();

        // Generate referral codes for admin
        $this->generateReferralCodes($admin);

        // Create test distributors with different balancing modes
        $distributors = $this->createTestDistributors();

        // Create a network structure
        $this->createNetworkStructure($distributors);

        // Generate some bonuses for testing
        $this->generateTestBonuses($distributors);

        $this->command->info('MLM test data created successfully!');
        $this->command->info("Admin: {$admin->email} / password: password");
        $this->command->info("Created " . count($distributors) . " test distributors");
    }

    /**
     * Create admin user
     */
    private function createAdminUser(): User
    {
        $admin = User::create([
            'id' => 'ADMIN001',
            'name' => 'System Administrator',
            'email' => 'admin@mlm.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'status' => 'active',
            'balancing_mode' => '1:1',
        ]);

        BinaryTree::create([
            'user_id' => $admin->id,
            'left_volume' => 0,
            'right_volume' => 0,
            'left_spillover' => 0,
            'right_spillover' => 0,
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        return $admin;
    }

    /**
     * Generate referral codes for admin
     */
    private function generateReferralCodes(User $admin): void
    {
        $referralCodeService = new ReferralCodeService();

        // Generate available codes
        $availableCodes = $referralCodeService->generateCodes($admin, 20);

        // Generate assigned codes for specific distributors
        $distributors = User::where('is_admin', false)->take(5)->get();
        foreach ($distributors as $distributor) {
            $assignedCodes = $referralCodeService->generateCodes($admin, 3, $distributor);
        }

        $this->command->info('Generated ' . count($availableCodes) . ' available codes and assigned codes to distributors');
    }

    /**
     * Create test distributors with different balancing modes
     */
    private function createTestDistributors(): array
    {
        $distributors = [];
        $balancingModes = ['1:1', '2:1', '3:1', 'carryover'];

        foreach ($balancingModes as $index => $mode) {
            $distributor = User::create([
                'id' => 'DIST' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'name' => ucfirst($mode) . ' Distributor ' . ($index + 1),
                'email' => strtolower($mode) . '_distributor' . ($index + 1) . '@test.com',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'status' => 'active',
                'balancing_mode' => $mode,
            ]);

            BinaryTree::create([
                'user_id' => $distributor->id,
                'left_volume' => rand(10, 50),
                'right_volume' => rand(10, 50),
                'left_spillover' => rand(0, 10),
                'right_spillover' => rand(0, 10),
                'total_left_volume' => rand(50, 100),
                'total_right_volume' => rand(50, 100),
                'left_consumed' => rand(0, 20),
                'right_consumed' => rand(0, 20),
            ]);

            $distributors[] = $distributor;
        }

        return $distributors;
    }

    /**
     * Create network structure
     */
    private function createNetworkStructure(array $distributors): void
    {
        $placementService = new BinaryTreePlacementService();

        // Create a multi-level network
        foreach ($distributors as $sponsor) {
            // Create 2-4 direct referrals for each distributor
            $directReferrals = rand(2, 4);

            for ($i = 0; $i < $directReferrals; $i++) {
                $referral = User::create([
                    'id' => 'REF' . $sponsor->id . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'name' => 'Referral ' . ($i + 1) . ' of ' . $sponsor->name,
                    'email' => 'ref' . $i + 1 . '_' . $sponsor->id . '@test.com',
                    'password' => Hash::make('password'),
                    'is_admin' => false,
                    'status' => 'active',
                    'balancing_mode' => $sponsor->balancing_mode,
                ]);

                // Place in binary tree
                $placementResult = $placementService->placeUser($referral, $sponsor, rand(0, 1) ? 'left' : 'right');

                // Create binary tree for referral
                BinaryTree::create([
                    'user_id' => $referral->id,
                    'left_volume' => rand(5, 25),
                    'right_volume' => rand(5, 25),
                    'left_spillover' => rand(0, 5),
                    'right_spillover' => rand(0, 5),
                    'total_left_volume' => rand(25, 50),
                    'total_right_volume' => rand(25, 50),
                    'left_consumed' => rand(0, 10),
                    'right_consumed' => rand(0, 10),
                ]);
            }
        }

        $this->command->info('Created network structure with referrals');
    }

    /**
     * Generate test bonuses
     */
    private function generateTestBonuses(array $distributors): void
    {
        foreach ($distributors as $distributor) {
            // Generate direct bonuses
            $directBonusCount = rand(1, 5);
            for ($i = 0; $i < $directBonusCount; $i++) {
                Bonus::create([
                    'user_id' => $distributor->id,
                    'amount' => 100.00,
                    'is_product' => $i % 5 === 0, // Every 5th is product
                    'reward_type' => 'direct',
                    'pair_count' => 1,
                    'description' => "Direct referral bonus #{$i}",
                    'status' => rand(0, 1) ? 'pending' : 'paid',
                ]);
            }

            // Generate pair bonuses
            $pairBonusCount = rand(2, 10);
            for ($i = 0; $i < $pairBonusCount; $i++) {
                Bonus::create([
                    'user_id' => $distributor->id,
                    'amount' => 100.00,
                    'is_product' => $i % 5 === 0, // Every 5th is product
                    'reward_type' => 'pair',
                    'pair_count' => 1,
                    'description' => "Pair matching bonus #{$i}",
                    'status' => rand(0, 1) ? 'pending' : 'paid',
                ]);
            }

            // Generate level bonuses
            $levelBonusCount = rand(1, 3);
            for ($i = 0; $i < $levelBonusCount; $i++) {
                Bonus::create([
                    'user_id' => $distributor->id,
                    'amount' => 50.00,
                    'is_product' => false,
                    'reward_type' => 'level',
                    'level_index' => rand(1, 5),
                    'pair_count' => 1,
                    'description' => "Level " . rand(1, 5) . " bonus",
                    'status' => rand(0, 1) ? 'pending' : 'paid',
                ]);
            }
        }

        $this->command->info('Generated test bonuses for distributors');
    }
}