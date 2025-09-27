<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\BinaryTree;
use App\Models\Bonus;
use App\Services\BinaryBalancerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BinaryBalancerServiceTest extends TestCase
{
    use RefreshDatabase;

    private BinaryBalancerService $service;
    private User $user;
    private User $sponsor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BinaryBalancerService();

        $this->sponsor = User::factory()->create();
        $this->user = User::factory()->create();

        // Create binary trees
        BinaryTree::create([
            'user_id' => $this->sponsor->id,
            'total_left_volume' => 10,
            'total_right_volume' => 10,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 1,
        ]);

        BinaryTree::create([
            'user_id' => $this->user->id,
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 1,
        ]);
    }

    public function test_place_user_creates_binary_tree_and_processes_balancer()
    {
        $this->service->placeUser($this->user, $this->sponsor, 'left');

        $this->assertDatabaseHas('binary_trees', [
            'user_id' => $this->user->id,
            'left_child_id' => null,
            'right_child_id' => null,
        ]);

        $this->user->refresh();
        $this->assertEquals($this->sponsor->id, $this->user->sponsor_id);
        $this->assertEquals('left', $this->user->placement_side);
    }

    public function test_process_levels_with_carryover_mode()
    {
        $user = User::factory()->create();
        $tree = BinaryTree::create([
            'user_id' => $user->id,
            'total_left_volume' => 5, // Level 1 quota = 2, so 5 >= 2
            'total_right_volume' => 3, // 3 >= 2
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 1,
        ]);

        // Debug: Check initial state
        $this->assertEquals(5, $tree->getAttributes()['total_left_volume']);
        $this->assertEquals(3, $tree->getAttributes()['total_right_volume']);
        $this->assertEquals(0, $tree->getAttributes()['left_consumed']);
        $this->assertEquals(0, $tree->getAttributes()['right_consumed']);
        $this->assertEquals(1, $tree->getAttributes()['level_index']);

        $this->service->processLevels($user);

        $tree->refresh();

        // Debug: Check what happened
        \Log::info('After processLevels', [
            'total_left_volume' => $tree->getAttributes()['total_left_volume'],
            'total_right_volume' => $tree->getAttributes()['total_right_volume'],
            'left_consumed' => $tree->getAttributes()['left_consumed'],
            'right_consumed' => $tree->getAttributes()['right_consumed'],
            'level_index' => $tree->getAttributes()['level_index'],
        ]);

        $this->assertGreaterThan(0, $tree->left_consumed); // Should have consumed some volume
        $this->assertGreaterThan(0, $tree->right_consumed); // Should have consumed some volume
    }

    public function test_process_levels_with_sufficient_volume()
    {
        $user = User::factory()->create();
        BinaryTree::create([
            'user_id' => $user->id,
            'total_left_volume' => 6, // Level 1 quota = 2, so 6 >= 2
            'total_right_volume' => 3, // 3 >= 2
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 1,
        ]);

        $this->service->processLevels($user);

        $tree = BinaryTree::where('user_id', $user->id)->first();
        $this->assertGreaterThan(0, $tree->left_consumed); // Should have consumed some volume
        $this->assertGreaterThan(0, $tree->right_consumed); // Should have consumed some volume
    }

    public function test_process_levels_with_massive_volume()
    {
        $user = User::factory()->create();
        BinaryTree::create([
            'user_id' => $user->id,
            'total_left_volume' => 100, // Much more than level quotas
            'total_right_volume' => 100,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 1,
        ]);

        $this->service->processLevels($user);

        $tree = BinaryTree::where('user_id', $user->id)->first();
        $this->assertGreaterThan(0, $tree->left_consumed); // Should have consumed some volume
        $this->assertGreaterThan(0, $tree->right_consumed); // Should have consumed some volume
        $this->assertGreaterThan(1, $tree->level_index); // Should have advanced levels
    }

    public function test_calculate_direct_bonus()
    {
        // Create direct referrals
        $leftReferral = User::factory()->create([
            'sponsor_id' => $this->sponsor->id,
            'placement_side' => 'left'
        ]);

        $rightReferral = User::factory()->create([
            'sponsor_id' => $this->sponsor->id,
            'placement_side' => 'right'
        ]);

        $this->service->calculateDirectBonus($this->sponsor);

        $bonuses = Bonus::where('user_id', $this->sponsor->id)
            ->where('reward_type', 'direct')
            ->get();

        $this->assertCount(1, $bonuses);
        $this->assertEquals(100.00, $bonuses->first()->amount);
        $this->assertEquals('Direct Referral Bonus ₱100 (Reward #1)', $bonuses->first()->description);
    }

    public function test_calculate_spillover_bonus()
    {
        // Create spillover referrals (non-direct)
        $leftChild = User::factory()->create([
            'sponsor_id' => $this->sponsor->id,
            'placement_side' => 'left'
        ]);

        $rightChild = User::factory()->create([
            'sponsor_id' => $this->sponsor->id,
            'placement_side' => 'right'
        ]);

        // Create spillover under left child
        $spilloverLeft = User::factory()->create([
            'sponsor_id' => $leftChild->id,
            'placement_side' => 'left'
        ]);

        $spilloverRight = User::factory()->create([
            'sponsor_id' => $rightChild->id,
            'placement_side' => 'right'
        ]);

        $this->service->calculateSpilloverBonus($this->sponsor);

        $bonuses = Bonus::where('user_id', $this->sponsor->id)
            ->where('reward_type', 'spillover')
            ->get();

        $this->assertCount(1, $bonuses);
        $this->assertEquals(100.00, $bonuses->first()->amount); // Should be ₱100, not ₱20
        $this->assertEquals('Spillover Bonus ₱100 (Reward #1)', $bonuses->first()->description);
    }

    public function test_process_levels_calculates_correctly()
    {
        $user = User::factory()->create();
        BinaryTree::where('user_id', $user->id)->update([
            'total_left_volume' => 6, // Level 1 quota = 2, so 6 >= 2
            'total_right_volume' => 3, // 3 >= 2
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 1,
        ]);

        $this->service->processLevels($user);

        // Verify that level processing occurred
        $tree = BinaryTree::where('user_id', $user->id)->first();
        $this->assertGreaterThan(0, $tree->left_consumed);
        $this->assertGreaterThan(0, $tree->right_consumed);
    }

    public function test_process_levels_with_one_sided_quota()
    {
        $user = User::factory()->create();
        BinaryTree::create([
            'user_id' => $user->id,
            'total_left_volume' => 8, // Level 3 quota = 8, so 8 >= 8
            'total_right_volume' => 0, // 0 < 8, so only left should trigger
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 3,
        ]);

        $this->service->processLevels($user);

        $tree = BinaryTree::where('user_id', $user->id)->first();
        $this->assertGreaterThan(0, $tree->left_consumed); // Should have consumed left volume
        $this->assertEquals(0, $tree->right_consumed); // Should not have consumed right volume
    }

    public function test_process_levels_with_simultaneous_both_sides()
    {
        $user = User::factory()->create();
        BinaryTree::create([
            'user_id' => $user->id,
            'total_left_volume' => 16, // Level 4 quota = 16, so 16 >= 16
            'total_right_volume' => 16, // 16 >= 16
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 4,
        ]);

        $this->service->processLevels($user);

        $tree = BinaryTree::where('user_id', $user->id)->first();
        $this->assertGreaterThan(0, $tree->left_consumed); // Should have consumed both sides
        $this->assertGreaterThan(0, $tree->right_consumed); // Should have consumed both sides
    }

    public function test_process_downline_quotas_for_uplines()
    {
        $upline1 = User::factory()->create();
        $upline2 = User::factory()->create();

        $this->user->update(['sponsor_id' => $upline1->id]);
        $upline1->update(['sponsor_id' => $upline2->id]);

        BinaryTree::create([
            'user_id' => $upline1->id,
            'total_left_volume' => 5,
            'total_right_volume' => 5,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 1,
        ]);

        BinaryTree::create([
            'user_id' => $upline2->id,
            'total_left_volume' => 10,
            'total_right_volume' => 10,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 1,
        ]);

        $this->service->processDownlineQuotasForUplines($this->user);

        // Check that level processing occurred for uplines
        $upline1Tree = BinaryTree::where('user_id', $upline1->id)->first();
        $upline2Tree = BinaryTree::where('user_id', $upline2->id)->first();

        $this->assertGreaterThan(0, $upline1Tree->left_consumed);
        $this->assertGreaterThan(0, $upline2Tree->left_consumed);
    }

    public function test_product_reward_every_5th_bonus()
    {
        $user = User::factory()->create();
        BinaryTree::create([
            'user_id' => $user->id,
            'total_left_volume' => 5, // Level 1 quota = 2, so 5 >= 2
            'total_right_volume' => 5, // 5 >= 2
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 1,
        ]);

        $this->service->processLevels($user);

        $bonuses = Bonus::where('user_id', $user->id)->where('reward_type', 'level')->get();

        // Check that bonuses were created and some are product rewards
        $this->assertGreaterThan(0, $bonuses->count());
        $productBonuses = $bonuses->where('is_product', true);
        $this->assertGreaterThan(0, $productBonuses->count());
    }
}