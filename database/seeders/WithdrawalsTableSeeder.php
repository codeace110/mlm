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
        $users = User::where('is_admin', false)->where('status', 'approved')->get();
        $methods = ['bank_transfer', 'paypal', 'gcash', 'paymaya'];
        $statuses = ['pending', 'approved', 'denied'];

        foreach ($users as $user) {
            // Create 1-3 random withdrawals per user
            $numWithdrawals = rand(1, 3);

            for ($i = 0; $i < $numWithdrawals; $i++) {
                $method = $methods[array_rand($methods)];

                // Ensure amount doesn't exceed user's balance for approved withdrawals
                $maxAmount = $user->account_balance;
                if ($maxAmount < 500) {
                    continue; // Skip if user doesn't have minimum balance
                }

                $amount = rand(500, min(10000, $maxAmount)); // Random amount between 500 and min(10000, balance)
                $status = $statuses[array_rand($statuses)];

                $accountDetails = [];
                switch ($method) {
                    case 'bank_transfer':
                        $accountDetails = [
                            'bank_name' => ['BDO', 'BPI', 'Metrobank', 'PNB', 'UnionBank'][array_rand(['BDO', 'BPI', 'Metrobank', 'PNB', 'UnionBank'])],
                            'account_number' => rand(1000000000, 9999999999),
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
                            'mobile_number' => $user->phone ?? '+63' . rand(900000000, 999999999),
                            'account_name' => $user->name,
                        ];
                        break;
                }

                $withdrawal = Withdrawal::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'method' => $method,
                    'account_details' => $accountDetails,
                    'status' => $status,
                    'created_at' => now()->subDays(rand(1, 60)), // Random date within last 60 days
                ]);

                // Deduct balance only for approved withdrawals (as per business logic)
                if ($status === 'approved') {
                    $user->decrement('account_balance', $amount);
                }
            }
        }

        // Ensure we have some pending withdrawals for testing
        $pendingUsers = $users->take(15); // First 15 users
        foreach ($pendingUsers as $user) {
            if ($user->withdrawals()->where('status', 'pending')->count() == 0) {
                // Ensure amount doesn't exceed user's current balance
                $maxAmount = $user->account_balance;
                if ($maxAmount >= 500) {
                    $amount = rand(500, min(5000, $maxAmount));

                    Withdrawal::create([
                        'user_id' => $user->id,
                        'amount' => $amount,
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
}