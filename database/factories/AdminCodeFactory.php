<?php

namespace Database\Factories;

use App\Models\AdminCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdminCode>
 */
class AdminCodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AdminCode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::upper(Str::random(8)),
            'status' => $this->faker->randomElement(['issued', 'unused', 'used']),
            'distributor_id' => User::factory(),
            'used_by_user_id' => null,
            'used_at' => null,
        ];
    }

    /**
     * Indicate that the admin code is issued.
     */
    public function issued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'issued',
            'distributor_id' => null,
        ]);
    }

    /**
     * Indicate that the admin code is unused.
     */
    public function unused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unused',
        ]);
    }

    /**
     * Indicate that the admin code is used.
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'used',
            'used_by_user_id' => User::factory(),
            'used_at' => now(),
        ]);
    }
}