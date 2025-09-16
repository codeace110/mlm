<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\BinaryTree;
use App\Models\Bonus;
use App\Services\BinaryBalancerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BinaryBalancerServiceTest extends TestCase
{
    use RefreshDatabase;

    private BinaryBalancerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BinaryBalancerService();
    }

    /** @test */
    public function it_issues_direct_bonus_when_user_has_both_left_and_right_directs()
    {
        $sponsor = User::factory()->create();
        $leftDirect = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        $rightDirect = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);

        $this->service->calculateDirectBonus($sponsor);

        $this->assertDatabaseHas('bonuses', [
            'user_id' => $sponsor->id,
            'amount' => 100.00,
            'reward_type' => 'direct',
            'is_product' => false,
        ]);

        $tree = BinaryTree::where('user_id', $sponsor->id)->first();
        $this->assertEquals(1, $tree->direct_pairs_paid);
    }

    /** @test */
    public function it_does_not_issue_duplicate_direct_bonuses()
    {
        $sponsor = User::factory()->create();
        User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);

        $this->service->calculateDirectBonus($sponsor);
        $this->service->calculateDirectBonus($sponsor); // Second call

        $this->assertDatabaseCount('bonuses', 1);
    }

    /** @test */
    public function it_issues_quota_bonus_when_volume_reaches_level_quota()
    {
        $user = User::factory()->create();
        $tree = BinaryTree::factory()->create([
            'user_id' => $user->id,
            'total_left_volume' => 2,
            'total_right_volume' => 2,
            'level_index' => 1,
        ]);

        $this->service->processLevels($user);

        $this->assertDatabaseHas('bonuses', [
            'user_id' => $user->id,
            'amount' => 100.00,
            'reward_type' => 'level',
            'level_index' => 1,
        ]);

        $tree->refresh();
        $this->assertEquals(2, $tree->level_index);
        $this->assertEquals(2, $tree->left_consumed);
        $this->assertEquals(2, $tree->right_consumed);
    }

    /** @test */
    public function it_consumes_only_the_side_that_reached_quota()
    {
        $user = User::factory()->create();
        BinaryTree::factory()->create([
            'user_id' => $user->id,
            'total_left_volume' => 4,
            'total_right_volume' => 1,
            'level_index' => 1,
        ]);

        $this->service->processLevels($user);

        $this->assertDatabaseHas('bonuses', [
            'user_id' => $user->id,
            'reward_type' => 'level',
            'level_index' => 1,
        ]);

        $tree = BinaryTree::where('user_id', $user->id)->first();
        $this->assertEquals(2, $tree->left_consumed);
        $this->assertEquals(0, $tree->right_consumed);
        $this->assertEquals(2, $tree->total_left_volume - $tree->left_consumed); // Carryover
    }

    /** @test */
    public function it_issues_only_one_bonus_when_both_sides_reach_quota_simultaneously()
    {
        $user = User::factory()->create();
        BinaryTree::factory()->create([
            'user_id' => $user->id,
            'total_left_volume' => 2,
            'total_right_volume' => 2,
            'level_index' => 1,
        ]);

        $this->service->processLevels($user);

        $this->assertDatabaseCount('bonuses', 1);
        $tree = BinaryTree::where('user_id', $user->id)->first();
        $this->assertEquals(2, $tree->left_consumed);
        $this->assertEquals(2, $tree->right_consumed);
    }

    /** @test */
    public function it_issues_product_reward_every_fifth_reward()
    {
        $user = User::factory()->create();
        $tree = BinaryTree::factory()->create([
            'user_id' => $user->id,
            'reward_count' => 4, // Next will be 5th
        ]);

        $this->service->issueReward($user, 'direct');

        $this->assertDatabaseHas('bonuses', [
            'user_id' => $user->id,
            'amount' => 0.00,
            'is_product' => true,
        ]);

        $tree->refresh();
        $this->assertEquals(5, $tree->reward_count);
    }

    /** @test */
    public function it_propagates_volume_up_the_upline()
    {
        $grandparent = User::factory()->create();
        $parent = User::factory()->create(['sponsor_id' => $grandparent->id, 'placement_side' => 'left']);
        $child = User::factory()->create(['sponsor_id' => $parent->id, 'placement_side' => 'left']);

        $this->service->propagateVolumeUp($child, 1);

        $parentTree = BinaryTree::where('user_id', $parent->id)->first();
        $this->assertEquals(1, $parentTree->total_left_volume);

        $grandparentTree = BinaryTree::where('user_id', $grandparent->id)->first();
        $this->assertEquals(1, $grandparentTree->total_left_volume);
    }

    /** @test */
    public function it_handles_massive_one_sided_growth()
    {
        $sponsor = User::factory()->create();
        $tree = BinaryTree::factory()->create(['user_id' => $sponsor->id]);

        // Simulate placing 8 users on right
        for ($i = 0; $i < 8; $i++) {
            $user = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);
            $this->service->propagateVolumeUp($user, 1);
            $this->service->calculateDirectBonus($sponsor);
            $this->service->processLevels($sponsor);
        }

        $tree->refresh();
        $this->assertEquals(8, $tree->total_right_volume);

        // Should have earned level bonuses
        $bonuses = Bonus::where('user_id', $sponsor->id)->where('reward_type', 'level')->count();
        $this->assertGreaterThan(0, $bonuses);
    }

    /** @test */
    public function it_is_idempotent_under_concurrency()
    {
        $user = User::factory()->create();
        BinaryTree::factory()->create([
            'user_id' => $user->id,
            'total_left_volume' => 2,
            'total_right_volume' => 2,
            'level_index' => 1,
        ]);

        // Simulate concurrent calls
        $this->service->processLevels($user);
        $this->service->processLevels($user); // Should not create duplicate

        $this->assertDatabaseCount('bonuses', 1);
    }

    /** @test */
    public function it_places_user_and_triggers_bonuses()
    {
        $sponsor = User::factory()->create();
        $newUser = User::factory()->create();

        $this->service->placeUser($newUser, $sponsor, 'left');

        $this->assertEquals($sponsor->id, $newUser->sponsor_id);
        $this->assertEquals('left', $newUser->placement_side);

        $tree = BinaryTree::where('user_id', $sponsor->id)->first();
        $this->assertEquals(1, $tree->total_left_volume);
    }
}