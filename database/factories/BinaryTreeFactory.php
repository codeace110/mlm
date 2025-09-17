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
            'left_child_id' => null,
            'right_child_id' => null,
            'left_volume' => 0,
            'right_volume' => 0,
            'left_spillover' => 0,
            'right_spillover' => 0,
            'carryover_left' => 0,
            'carryover_right' => 0,
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 1,
            'reward_count' => 0,
            'direct_pairs_paid' => 0,
        ];
    }
}