<?php

namespace Database\Factories;

use App\Models\Bonus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BonusFactory extends Factory
{
    protected $model = Bonus::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 500),
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pending', 'paid', 'cancelled']),
        ];
    }
}