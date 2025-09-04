<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\BinaryTree;
use App\Services\BinaryTreeService;

class BinaryTreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_placement_in_binary_tree()
    {
        $sponsor = User::factory()->create();
        $newUser = User::factory()->create();

        $service = new BinaryTreeService();
        $service->placeUserInTree($newUser, $sponsor);

        $tree = BinaryTree::where('user_id', $sponsor->id)->first();
        $this->assertNotNull($tree);
        $this->assertEquals($newUser->id, $tree->left_child_id);
        $this->assertEquals(100, $tree->left_volume);
    }

    public function test_second_user_placement_right()
    {
        $sponsor = User::factory()->create();
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $service = new BinaryTreeService();
        $service->placeUserInTree($firstUser, $sponsor);
        $service->placeUserInTree($secondUser, $sponsor);

        $tree = BinaryTree::where('user_id', $sponsor->id)->first();
        $this->assertEquals($firstUser->id, $tree->left_child_id);
        $this->assertEquals($secondUser->id, $tree->right_child_id);
        $this->assertEquals(100, $tree->left_volume);
        $this->assertEquals(100, $tree->right_volume);
    }
}