<?php

namespace Database\Factories;

use App\Models\BinaryTree;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BinaryTreeFactory extends Factory
{
    protected $model = BinaryTree::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'parent_id' => null,
            'total_left_volume' => $this->faker->numberBetween(0, 1000),
            'total_right_volume' => $this->faker->numberBetween(0, 1000),
            'left_consumed' => $this->faker->numberBetween(0, 500),
            'right_consumed' => $this->faker->numberBetween(0, 500),
            'level_index' => $this->faker->numberBetween(1, 10),
            'reward_count' => $this->faker->numberBetween(0, 50),
            'direct_pairs_paid' => $this->faker->numberBetween(0, 20),
        ];
    }
}