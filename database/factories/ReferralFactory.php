<?php

namespace Database\Factories;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferralFactory extends Factory
{
    protected $model = Referral::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'sponsor_id' => User::factory(),
            'placement_side' => $this->faker->randomElement(['left', 'right']),
            'level' => $this->faker->numberBetween(1, 5),
            'commission' => $this->faker->randomFloat(2, 0, 100),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'approved_by' => null,
        ];
    }
}