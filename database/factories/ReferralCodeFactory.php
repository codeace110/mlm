<?php

namespace Database\Factories;

use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferralCodeFactory extends Factory
{
    protected $model = ReferralCode::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->regexify('[A-Z0-9]{15}'),
            'generated_by' => User::factory(),
            'assigned_to' => User::factory(),
            'used_by' => null,
            'status' => $this->faker->randomElement(['available', 'assigned', 'used']),
            'batch_id' => $this->faker->numberBetween(1, 1000),
        ];
    }
}