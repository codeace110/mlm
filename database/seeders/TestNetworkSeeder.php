<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\BinaryTree;
use App\Services\BinaryBalancerService;

class TestNetworkSeeder extends Seeder
{
    public function run()
    {
        // Find existing test user or create new one
        $testUser = User::where('email', 'like', 'balancing_test%')->first();

        if (!$testUser) {
            $testUser = User::factory()->create([
                'name' => 'Test User for Balancing',
                'email' => 'balancing_test_' . time() . '@example.com',
                'password' => bcrypt('password'),
                'referral_code' => 'BALTEST' . time(),
                'is_admin' => false,
                'status' => 'approved',
                'account_balance' => 1000.00,
            ]);
        }

        // Create BinaryTree for test user if not exists
        $testTree = BinaryTree::firstOrCreate(['user_id' => $testUser->id]);

        $this->command->info("Setting up network for {$testUser->email}");

        // Get or create direct referrals
        $directReferrals = User::where('sponsor_id', $testUser->id)->get()->toArray();
        $balancerService = new BinaryBalancerService();

        if (count($directReferrals) < 2) {
            // Create missing direct referrals
            if (count($directReferrals) === 0) {
                // Left direct
                $leftUser = User::factory()->create([
                    'name' => "Left Direct",
                    'email' => "left_direct_" . time() . "@example.com",
                    'status' => 'approved',
                    'account_balance' => 500.00,
                ]);
                $this->command->info("Placing left direct user...");
                $balancerService->placeUser($leftUser, $testUser, 'left');
                $directReferrals[] = $leftUser;
                $this->command->info("Created left direct referral: {$leftUser->name}");
            }

            if (count($directReferrals) === 1) {
                // Right direct
                $rightUser = User::factory()->create([
                    'name' => "Right Direct",
                    'email' => "right_direct_" . time() . "@example.com",
                    'status' => 'approved',
                    'account_balance' => 500.00,
                ]);
                $this->command->info("Placing right direct user...");
                $balancerService->placeUser($rightUser, $testUser, 'right');
                $directReferrals[] = $rightUser;
                $this->command->info("Created right direct referral: {$rightUser->name}");
            }
        } else {
            $this->command->info("Direct referrals already exist");
        }

        // Get direct referrals as User objects
        $directs = User::where('sponsor_id', $testUser->id)->get();
        $leftUser = $directs->where('placement_side', 'left')->first();
        $rightUser = $directs->where('placement_side', 'right')->first();

        $this->command->info("Creating downlines for each direct referral");

        // Create downlines to test balancing with pairs
        $this->command->info("Creating downlines for testing balancing");

        // Check existing downlines for left direct
        $existingLeftDownlines = User::where('sponsor_id', $leftUser->id)->count();
        if ($existingLeftDownlines < 2) {
            // Create left downline under left direct
            $leftDownline = User::factory()->create([
                'name' => "Left Downline " . ($existingLeftDownlines + 1),
                'email' => "left_downline" . ($existingLeftDownlines + 1) . "_" . time() . "@example.com",
                'status' => 'approved',
                'account_balance' => 200.00,
            ]);
            $this->command->info("Placing left downline under left direct...");
            $balancerService->placeUser($leftDownline, $leftUser, 'left');
            $this->command->info("Created left downline: {$leftDownline->name}");

            // Create right downline under left direct
            $rightDownlineLeft = User::factory()->create([
                'name' => "Left Direct Right Downline",
                'email' => "left_direct_right_downline_" . time() . "@example.com",
                'status' => 'approved',
                'account_balance' => 200.00,
            ]);
            $this->command->info("Placing right downline under left direct...");
            $balancerService->placeUser($rightDownlineLeft, $leftUser, 'right');
            $this->command->info("Created right downline under left direct: {$rightDownlineLeft->name}");
        }

        // Check existing downlines for right direct
        $existingRightDownlines = User::where('sponsor_id', $rightUser->id)->count();
        if ($existingRightDownlines < 2) {
            // Create left downline under right direct
            $leftDownlineRight = User::factory()->create([
                'name' => "Right Direct Left Downline",
                'email' => "right_direct_left_downline_" . time() . "@example.com",
                'status' => 'approved',
                'account_balance' => 200.00,
            ]);
            $this->command->info("Placing left downline under right direct...");
            $balancerService->placeUser($leftDownlineRight, $rightUser, 'left');
            $this->command->info("Created left downline under right direct: {$leftDownlineRight->name}");

            // Create right downline under right direct
            $rightDownline = User::factory()->create([
                'name' => "Right Downline " . ($existingRightDownlines + 1),
                'email' => "right_downline" . ($existingRightDownlines + 1) . "_" . time() . "@example.com",
                'status' => 'approved',
                'account_balance' => 200.00,
            ]);
            $this->command->info("Placing right downline under right direct...");
            $balancerService->placeUser($rightDownline, $rightUser, 'right');
            $this->command->info("Created right downline: {$rightDownline->name}");
        }

        $this->command->info("Binary Balancer Service automatically calculated bonuses during placement");

        $this->command->info("Test network setup complete!");
        $this->command->info("Test user: {$testUser->email}");
        $this->command->info("Direct referrals: " . count($directReferrals));
        $this->command->info("Total users created: " . (1 + count($directReferrals) + User::where('sponsor_id', '!=', null)->count()));
    }
}