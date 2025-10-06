<?php

namespace Database\Factories;

use App\Models\Earning;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EarningFactory extends Factory
{
    protected $model = Earning::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'type' => $this->faker->randomElement(['referral', 'binary', 'matching', 'leadership']),
            'description' => $this->faker->sentence(),
        ];
    }
}