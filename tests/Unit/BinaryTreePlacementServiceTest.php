<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\BinaryTree;
use App\Services\BinaryTreePlacementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BinaryTreePlacementServiceTest extends TestCase
{
    use RefreshDatabase;

    private BinaryTreePlacementService $service;
    private User $sponsor;
    private User $newUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BinaryTreePlacementService();

        $this->sponsor = User::factory()->create();
        $this->newUser = User::factory()->create();

        // Create binary tree for sponsor
        BinaryTree::create([
            'user_id' => $this->sponsor->id,
            'left_volume' => 0,
            'right_volume' => 0,
            'carryover_left' => 0,
            'carryover_right' => 0,
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);
    }

    public function test_place_user_directly_under_sponsor_preferred_left()
    {
        $result = $this->service->placeUser($this->newUser, $this->sponsor, 'left');

        $this->assertTrue($result['placed']);
        $this->assertEquals('left', $result['side']);
        $this->assertEquals($this->sponsor->id, $result['parent_id']);
        $this->assertEquals('direct_preferred', $result['method']);

        $sponsorTree = BinaryTree::where('user_id', $this->sponsor->id)->first();
        $this->assertEquals($this->newUser->id, $sponsorTree->left_child_id);

        $this->newUser->refresh();
        $this->assertEquals($this->sponsor->id, $this->newUser->sponsor_id);
        $this->assertEquals('left', $this->newUser->placement_side);
    }

    public function test_place_user_directly_under_sponsor_preferred_right()
    {
        $result = $this->service->placeUser($this->newUser, $this->sponsor, 'right');

        $this->assertTrue($result['placed']);
        $this->assertEquals('right', $result['side']);
        $this->assertEquals($this->sponsor->id, $result['parent_id']);
        $this->assertEquals('direct_preferred', $result['method']);

        $sponsorTree = BinaryTree::where('user_id', $this->sponsor->id)->first();
        $this->assertEquals($this->newUser->id, $sponsorTree->right_child_id);
    }

    public function test_place_user_directly_under_sponsor_first_available()
    {
        $result = $this->service->placeUser($this->newUser, $this->sponsor);

        $this->assertTrue($result['placed']);
        $this->assertEquals('left', $result['side']);
        $this->assertEquals($this->sponsor->id, $result['parent_id']);
        $this->assertEquals('direct_available', $result['method']);
    }

    public function test_place_user_with_spillover()
    {
        // Fill both positions under sponsor
        $leftChild = User::factory()->create();
        $rightChild = User::factory()->create();

        $sponsorTree = BinaryTree::where('user_id', $this->sponsor->id)->first();
        $sponsorTree->update([
            'left_child_id' => $leftChild->id,
            'right_child_id' => $rightChild->id
        ]);

        // Create trees for children
        BinaryTree::create([
            'user_id' => $leftChild->id,
            'left_volume' => 0,
            'right_volume' => 0,
            'carryover_left' => 0,
            'carryover_right' => 0,
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        BinaryTree::create([
            'user_id' => $rightChild->id,
            'left_volume' => 0,
            'right_volume' => 0,
            'carryover_left' => 0,
            'carryover_right' => 0,
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        $result = $this->service->placeUser($this->newUser, $this->sponsor, 'left');

        $this->assertTrue($result['placed']);
        $this->assertEquals('spillover_recursive', $result['method']);

        // Should be placed under left child
        $leftChildTree = BinaryTree::where('user_id', $leftChild->id)->first();
        $this->assertEquals($this->newUser->id, $leftChildTree->left_child_id);
    }

    public function test_place_user_with_forced_placement()
    {
        // Create a deep tree structure
        $level1Left = User::factory()->create();
        $level1Right = User::factory()->create();
        $level2Left = User::factory()->create();

        $sponsorTree = BinaryTree::where('user_id', $this->sponsor->id)->first();
        $sponsorTree->update([
            'left_child_id' => $level1Left->id,
            'right_child_id' => $level1Right->id
        ]);

        // Fill level 1 left completely
        BinaryTree::create([
            'user_id' => $level1Left->id,
            'left_child_id' => $level2Left->id,
            'right_child_id' => User::factory()->create()->id,
            'left_volume' => 0,
            'right_volume' => 0,
            'carryover_left' => 0,
            'carryover_right' => 0,
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        BinaryTree::create([
            'user_id' => $level1Right->id,
            'left_volume' => 0,
            'right_volume' => 0,
            'carryover_left' => 0,
            'carryover_right' => 0,
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        BinaryTree::create([
            'user_id' => $level2Left->id,
            'left_volume' => 0,
            'right_volume' => 0,
            'carryover_left' => 0,
            'carryover_right' => 0,
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        $result = $this->service->placeUser($this->newUser, $this->sponsor, 'left');

        $this->assertTrue($result['placed']);
        $this->assertContains($result['method'], ['forced_spillover', 'forced_deep', 'forced_direct']);
    }

    public function test_get_placement_stats()
    {
        // Create a network structure
        $leftChild = User::factory()->create();
        $rightChild = User::factory()->create();
        $leftGrandChild = User::factory()->create();

        $sponsorTree = BinaryTree::where('user_id', $this->sponsor->id)->first();
        $sponsorTree->update([
            'left_child_id' => $leftChild->id,
            'right_child_id' => $rightChild->id,
            'left_volume' => 10,
            'right_volume' => 8,
            'carryover_left' => 2,
            'carryover_right' => 1,
        ]);

        BinaryTree::create([
            'user_id' => $leftChild->id,
            'left_child_id' => $leftGrandChild->id,
            'left_volume' => 5,
            'right_volume' => 0,
            'carryover_left' => 0,
            'carryover_right' => 0,
        ]);

        BinaryTree::create([
            'user_id' => $rightChild->id,
            'left_volume' => 3,
            'right_volume' => 0,
            'carryover_left' => 0,
            'carryover_right' => 0,
        ]);

        BinaryTree::create([
            'user_id' => $leftGrandChild->id,
            'left_volume' => 2,
            'right_volume' => 0,
            'carryover_left' => 0,
            'carryover_right' => 0,
        ]);

        $stats = $this->service->getPlacementStats($this->sponsor);

        $this->assertEquals(2, $stats['left_children']);
        $this->assertEquals(1, $stats['right_children']);
        $this->assertEquals(3, $stats['total_downline']);
        $this->assertEquals(10.0, $stats['left_volume']);
        $this->assertEquals(8.0, $stats['right_volume']);
        $this->assertEquals(2.0, $stats['left_carryover']);
        $this->assertEquals(1.0, $stats['right_carryover']);
    }

    public function test_get_weaker_leg()
    {
        $tree = BinaryTree::where('user_id', $this->sponsor->id)->first();

        // Test with equal volumes
        $tree->update(['left_volume' => 5, 'right_volume' => 5]);
        $this->assertEquals('left', $this->service->getWeakerLeg($tree));

        // Test with left weaker
        $tree->update(['left_volume' => 3, 'right_volume' => 7]);
        $this->assertEquals('left', $this->service->getWeakerLeg($tree));

        // Test with right weaker
        $tree->update(['left_volume' => 8, 'right_volume' => 4]);
        $this->assertEquals('right', $this->service->getWeakerLeg($tree));
    }

    public function test_ensure_tree_exists()
    {
        $user = User::factory()->create();

        // Ensure method is private, test indirectly
        $this->service->placeUser($user, $this->sponsor);

        $this->assertDatabaseHas('binary_trees', [
            'user_id' => $user->id
        ]);
    }

    public function test_record_placement_history()
    {
        // This is tested indirectly through placeUser method
        $result = $this->service->placeUser($this->newUser, $this->sponsor, 'left');

        $this->assertDatabaseHas('users', [
            'id' => $this->newUser->id,
            'sponsor_id' => $this->sponsor->id,
            'placement_side' => 'left'
        ]);
    }
}