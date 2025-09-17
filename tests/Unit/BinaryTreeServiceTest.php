<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\BinaryTree;
use App\Services\BinaryTreeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BinaryTreeServiceTest extends TestCase
{
    use RefreshDatabase;

    private BinaryTreeService $binaryTreeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->binaryTreeService = new BinaryTreeService();
    }

    /** @test */
    public function it_builds_binary_tree_for_view_with_no_children()
    {
        $user = User::factory()->create(['name' => 'Root User']);

        $tree = $this->binaryTreeService->buildBinaryTreeForView($user);

        $this->assertNotNull($tree);
        $this->assertEquals('Root User', $tree['name']);
        $this->assertEquals($user->id, $tree['id']);
        $this->assertEquals(1, $tree['level']);
        $this->assertEquals(0, $tree['left_volume']);
        $this->assertEquals(0, $tree['right_volume']);
        $this->assertEquals(0, $tree['carryover_left']);
        $this->assertEquals(0, $tree['carryover_right']);
        $this->assertCount(2, $tree['children']);
        $this->assertNull($tree['children'][0]);
        $this->assertNull($tree['children'][1]);
    }

    /** @test */
    public function it_builds_binary_tree_for_view_with_existing_binary_tree_record()
    {
        $user = User::factory()->create(['name' => 'Root User']);

        // Create BinaryTree record with volumes
        BinaryTree::create([
            'user_id' => $user->id,
            'total_left_volume' => 5,
            'total_right_volume' => 3,
            'left_consumed' => 2,
            'right_consumed' => 1,
            'left_child_id' => null,
            'right_child_id' => null,
        ]);

        $tree = $this->binaryTreeService->buildBinaryTreeForView($user);

        $this->assertEquals(5, $tree['left_volume']);
        $this->assertEquals(3, $tree['right_volume']);
        $this->assertEquals(3, $tree['carryover_left']); // 5 - 2
        $this->assertEquals(2, $tree['carryover_right']); // 3 - 1
    }

    /** @test */
    public function it_builds_binary_tree_for_view_with_direct_children()
    {
        $root = User::factory()->create(['name' => 'Root User']);
        $leftChild = User::factory()->create(['name' => 'Left Child']);
        $rightChild = User::factory()->create(['name' => 'Right Child']);

        // Create BinaryTree record with children
        BinaryTree::create([
            'user_id' => $root->id,
            'left_child_id' => $leftChild->id,
            'right_child_id' => $rightChild->id,
            'total_left_volume' => 2,
            'total_right_volume' => 1,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        $tree = $this->binaryTreeService->buildBinaryTreeForView($root);

        $this->assertEquals('Root User', $tree['name']);
        $this->assertCount(2, $tree['children']);

        // Left child
        $this->assertNotNull($tree['children'][0]);
        $this->assertEquals('Left Child', $tree['children'][0]['name']);
        $this->assertEquals(2, $tree['children'][0]['level']);

        // Right child
        $this->assertNotNull($tree['children'][1]);
        $this->assertEquals('Right Child', $tree['children'][1]['name']);
        $this->assertEquals(2, $tree['children'][1]['level']);
    }

    /** @test */
    public function it_builds_binary_tree_for_view_with_fallback_to_sponsor_relationship()
    {
        $root = User::factory()->create(['name' => 'Root User']);
        $leftChild = User::factory()->create([
            'name' => 'Left Child',
            'sponsor_id' => $root->id
        ]);
        $rightChild = User::factory()->create([
            'name' => 'Right Child',
            'sponsor_id' => $root->id
        ]);

        // No BinaryTree record, should fallback to sponsor relationship
        $tree = $this->binaryTreeService->buildBinaryTreeForView($root);

        $this->assertEquals('Root User', $tree['name']);
        $this->assertCount(2, $tree['children']);

        // Should have children based on sponsor relationship
        $this->assertNotNull($tree['children'][0]);
        $this->assertNotNull($tree['children'][1]);
    }

    /** @test */
    public function it_respects_max_depth_parameter()
    {
        $root = User::factory()->create(['name' => 'Root User']);
        $child = User::factory()->create(['name' => 'Child']);
        $grandchild = User::factory()->create(['name' => 'Grandchild']);

        // Create tree structure
        BinaryTree::create([
            'user_id' => $root->id,
            'left_child_id' => $child->id,
        ]);

        BinaryTree::create([
            'user_id' => $child->id,
            'left_child_id' => $grandchild->id,
        ]);

        // Test with maxDepth = 2 (should include child but not grandchild)
        $tree = $this->binaryTreeService->buildBinaryTreeForView($root, 0, 2);

        $this->assertEquals('Root User', $tree['name']);
        $this->assertNotNull($tree['children'][0]);
        $this->assertEquals('Child', $tree['children'][0]['name']);

        // Child should have null children since maxDepth = 2
        $this->assertNull($tree['children'][0]['children'][0]);
    }

    /** @test */
    public function it_handles_complex_tree_structure()
    {
        // Create a more complex tree
        $root = User::factory()->create(['name' => 'Root']);

        $left1 = User::factory()->create(['name' => 'Left1']);
        $right1 = User::factory()->create(['name' => 'Right1']);

        $left1_left = User::factory()->create(['name' => 'Left1_Left']);
        $left1_right = User::factory()->create(['name' => 'Left1_Right']);

        $right1_left = User::factory()->create(['name' => 'Right1_Left']);

        // Set up tree relationships
        BinaryTree::create([
            'user_id' => $root->id,
            'left_child_id' => $left1->id,
            'right_child_id' => $right1->id,
            'total_left_volume' => 3,
            'total_right_volume' => 2,
            'left_consumed' => 1,
            'right_consumed' => 0,
        ]);

        BinaryTree::create([
            'user_id' => $left1->id,
            'left_child_id' => $left1_left->id,
            'right_child_id' => $left1_right->id,
            'total_left_volume' => 2,
            'total_right_volume' => 1,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        BinaryTree::create([
            'user_id' => $right1->id,
            'left_child_id' => $right1_left->id,
            'right_child_id' => null,
            'total_left_volume' => 1,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        $tree = $this->binaryTreeService->buildBinaryTreeForView($root, 0, 3);

        // Verify root
        $this->assertEquals('Root', $tree['name']);
        $this->assertEquals(3, $tree['left_volume']);
        $this->assertEquals(2, $tree['right_volume']);
        $this->assertEquals(2, $tree['carryover_left']); // 3 - 1
        $this->assertEquals(2, $tree['carryover_right']); // 2 - 0

        // Verify left child (Left1)
        $leftChild = $tree['children'][0];
        $this->assertEquals('Left1', $leftChild['name']);
        $this->assertEquals(2, $leftChild['left_volume']);
        $this->assertEquals(1, $leftChild['right_volume']);

        // Verify left child's children
        $this->assertNotNull($leftChild['children'][0]);
        $this->assertEquals('Left1_Left', $leftChild['children'][0]['name']);
        $this->assertNotNull($leftChild['children'][1]);
        $this->assertEquals('Left1_Right', $leftChild['children'][1]['name']);

        // Verify right child (Right1)
        $rightChild = $tree['children'][1];
        $this->assertEquals('Right1', $rightChild['name']);
        $this->assertEquals(1, $rightChild['left_volume']);
        $this->assertEquals(0, $rightChild['right_volume']);

        // Verify right child's children
        $this->assertNotNull($rightChild['children'][0]);
        $this->assertEquals('Right1_Left', $rightChild['children'][0]['name']);
        $this->assertNull($rightChild['children'][1]);
    }

    /** @test */
    public function it_returns_null_when_depth_exceeds_max_depth()
    {
        $user = User::factory()->create();

        $tree = $this->binaryTreeService->buildBinaryTreeForView($user, 5, 3);

        $this->assertNull($tree);
    }

    /** @test */
    public function it_handles_missing_child_users_gracefully()
    {
        $root = User::factory()->create(['name' => 'Root']);

        // Disable FK checks to allow invalid child ID
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Create BinaryTree with invalid child IDs
        BinaryTree::create([
            'user_id' => $root->id,
            'left_child_id' => 99999, // Non-existent user
            'right_child_id' => null,
        ]);

        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $tree = $this->binaryTreeService->buildBinaryTreeForView($root);

        $this->assertEquals('Root', $tree['name']);
        $this->assertNull($tree['children'][0]); // Should be null for invalid child
        $this->assertNull($tree['children'][1]);
    }
}