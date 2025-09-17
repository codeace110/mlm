<?php

namespace Database\Factories;

use App\Models\Withdrawal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WithdrawalFactory extends Factory
{
    protected $model = Withdrawal::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 1000),
            'method' => $this->faker->randomElement(['cebuana_lhuillier', 'mlhuillier', 'palawan_pawnshop', 'gcash', 'paymaya']),
            'account_details' => json_encode([
                'account_number' => $this->faker->bankAccountNumber(),
                'account_name' => $this->faker->name(),
            ]),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'completed']),
        ];
    }
}