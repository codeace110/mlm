<?php

namespace Tests\Unit;

use App\Models\BinaryTree;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BinaryTreeModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_binary_tree_has_fillable_attributes()
    {
        $fillable = [
            'user_id',
            'parent_id',
            'total_left_volume',
            'total_right_volume',
            'left_consumed',
            'right_consumed',
            'level_index',
            'reward_count',
            'direct_pairs_paid',
            'spillover_pairs_paid',
            'left_spillover',
            'right_spillover',
            'left_child_id',
            'right_child_id',
            'placement_side',
        ];

        $this->assertEquals($fillable, (new BinaryTree)->getFillable());
    }

    public function test_binary_tree_has_correct_casts()
    {
        $casts = [
            'id' => 'int',
            'left_spillover' => 'integer',
            'right_spillover' => 'integer',
            'total_left_volume' => 'integer',
            'total_right_volume' => 'integer',
            'left_consumed' => 'integer',
            'right_consumed' => 'integer',
            'level_index' => 'integer',
            'reward_count' => 'integer',
            'direct_pairs_paid' => 'integer',
            'spillover_pairs_paid' => 'integer',
        ];

        $this->assertEquals($casts, (new BinaryTree)->getCasts());
    }

    public function test_binary_tree_user_relationship()
    {
        $user = User::factory()->create();
        $binaryTree = BinaryTree::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $binaryTree->user);
        $this->assertEquals($user->id, $binaryTree->user->id);
    }

    public function test_binary_tree_left_child_relationship()
    {
        $leftChild = User::factory()->create();
        $binaryTree = BinaryTree::factory()->create(['left_child_id' => $leftChild->id]);

        $this->assertInstanceOf(User::class, $binaryTree->leftChild);
        $this->assertEquals($leftChild->id, $binaryTree->leftChild->id);
    }

    public function test_binary_tree_right_child_relationship()
    {
        $rightChild = User::factory()->create();
        $binaryTree = BinaryTree::factory()->create(['right_child_id' => $rightChild->id]);

        $this->assertInstanceOf(User::class, $binaryTree->rightChild);
        $this->assertEquals($rightChild->id, $binaryTree->rightChild->id);
    }

    public function test_binary_tree_mass_assignment()
    {
        $data = [
            'user_id' => User::factory()->create()->id,
            'total_left_volume' => 100,
            'total_right_volume' => 200,
            'level_index' => 3,
            'reward_count' => 5,
        ];

        $binaryTree = BinaryTree::create($data);

        $this->assertEquals($data['user_id'], $binaryTree->user_id);
        $this->assertEquals(100, $binaryTree->total_left_volume);
        $this->assertEquals(200, $binaryTree->total_right_volume);
        $this->assertEquals(3, $binaryTree->level_index);
        $this->assertEquals(5, $binaryTree->reward_count);
    }

    public function test_binary_tree_casts_work_correctly()
    {
        $binaryTree = BinaryTree::factory()->create([
            'total_left_volume' => '150',
            'total_right_volume' => '300',
            'level_index' => '2',
            'reward_count' => '10',
        ]);

        $this->assertEquals(150, $binaryTree->total_left_volume);
        $this->assertEquals(300, $binaryTree->total_right_volume);
        $this->assertEquals(2, $binaryTree->level_index);
        $this->assertEquals(10, $binaryTree->reward_count);
    }

    public function test_binary_tree_can_have_null_children()
    {
        $binaryTree = BinaryTree::factory()->create([
            'left_child_id' => null,
            'right_child_id' => null,
        ]);

        $this->assertNull($binaryTree->leftChild);
        $this->assertNull($binaryTree->rightChild);
    }
}