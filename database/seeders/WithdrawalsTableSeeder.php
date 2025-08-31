<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Withdrawal;
use App\Models\User;

class WithdrawalsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('is_admin', false)->get();
        $methods = ['bank_transfer', 'paypal', 'gcash', 'paymaya'];
        $statuses = ['pending', 'approved', 'denied'];

        foreach ($users as $user) {
            // Create 1-3 random withdrawals per user
            $numWithdrawals = rand(1, 3);

            for ($i = 0; $i < $numWithdrawals; $i++) {
                $method = $methods[array_rand($methods)];
                $amount = rand(500, 10000); // Random amount between 500 and 10000
                $status = $statuses[array_rand($statuses)];

                $accountDetails = [];
                switch ($method) {
                    case 'bank_transfer':
                        $accountDetails = [
                            'bank_name' => 'Sample Bank',
                            'account_number' => '1234567890',
                            'account_name' => $user->name,
                        ];
                        break;
                    case 'paypal':
                        $accountDetails = [
                            'email' => $user->email,
                        ];
                        break;
                    case 'gcash':
                    case 'paymaya':
                        $accountDetails = [
                            'mobile_number' => '+63' . rand(900000000, 999999999),
                            'account_name' => $user->name,
                        ];
                        break;
                }

                Withdrawal::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'method' => $method,
                    'account_details' => $accountDetails,
                    'status' => $status,
                    'created_at' => now()->subDays(rand(1, 60)), // Random date within last 60 days
                ]);
            }
        }

        // Ensure we have some pending withdrawals for testing
        $pendingUsers = $users->take(15); // First 15 users
        foreach ($pendingUsers as $user) {
            if ($user->withdrawals()->where('status', 'pending')->count() == 0) {
                Withdrawal::create([
                    'user_id' => $user->id,
                    'amount' => rand(1000, 5000),
                    'method' => 'bank_transfer',
                    'account_details' => [
                        'bank_name' => 'Test Bank',
                        'account_number' => '9876543210',
                        'account_name' => $user->name,
                    ],
                    'status' => 'pending',
                    'created_at' => now()->subDays(rand(1, 7)),
                ]);
            }
        }
    }
}