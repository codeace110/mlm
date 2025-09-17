<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\BinaryTree;
use App\Services\BinaryTreeService;
use Illuminate\Support\Facades\Hash;

class TestBinaryTreeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a test sponsor user
        $sponsor = User::create([
            'name' => 'Test Sponsor',
            'email' => 'sponsor@test.com',
            'password' => Hash::make('password'),
            'referral_code' => 'SPONSOR123',
            'phone' => '09123456789',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'account_balance' => 0,
        ]);

        // Create 5 direct referrals to test spillover
        $binaryService = new BinaryTreeService();

        for ($i = 1; $i <= 5; $i++) {
            $referral = User::create([
                'name' => "Direct Referral {$i}",
                'email' => "direct{$i}@test.com",
                'password' => Hash::make('password'),
                'referral_code' => "DIRECT{$i}123",
                'phone' => '0912345678' . $i,
                'address' => 'Test Address',
                'city' => 'Test City',
                'province' => 'Test Province',
                'sponsor_id' => $sponsor->id,
                'account_balance' => 0,
            ]);

            // Place in binary tree - first two will go to left/right, rest will spillover
            $binaryService->placeUserInTree($referral, $sponsor, $i <= 2 ? ($i == 1 ? 'left' : 'right') : null);
        }

        // Create some downline users for the direct referrals to test pair bonuses
        $directReferrals = User::where('sponsor_id', $sponsor->id)->get();

        foreach ($directReferrals as $index => $directReferral) {
            // Create 2-3 downlines for each direct referral to create pairs
            $numDownlines = rand(2, 3);
            for ($j = 1; $j <= $numDownlines; $j++) {
                $downline = User::create([
                    'name' => "Downline {$index}-{$j}",
                    'email' => "downline{$index}-{$j}@test.com",
                    'password' => Hash::make('password'),
                    'referral_code' => "DOWN{$index}-{$j}123",
                    'phone' => '091111111' . $index . $j,
                    'address' => 'Test Address',
                    'city' => 'Test City',
                    'province' => 'Test Province',
                    'sponsor_id' => $directReferral->id,
                    'account_balance' => 0,
                ]);

                // Place in binary tree
                $binaryService->placeUserInTree($downline, $directReferral, $j == 1 ? 'left' : 'right');
            }
        }

        $this->command->info('Test binary tree created with 5 direct referrals and spillover demonstration');
        $this->command->info('Login with: sponsor@test.com / password');
    }
}