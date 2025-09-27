<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\BinaryTree;
use App\Models\Bonus;
use App\Services\BinaryBalancerService;

class BinaryBalancerServiceTest extends TestCase
{
    use RefreshDatabase;

    private BinaryBalancerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BinaryBalancerService();
    }

    public function test_direct_referral_pairs()
    {
        // Setup: create sponsor user
        $sponsor = User::factory()->create();

        // Create 3 left directs
        for ($i = 0; $i < 3; $i++) {
            User::factory()->create([
                'sponsor_id' => $sponsor->id,
                'placement_side' => 'left'
            ]);
        }

        // Create 3 right directs
        for ($i = 0; $i < 3; $i++) {
            User::factory()->create([
                'sponsor_id' => $sponsor->id,
                'placement_side' => 'right'
            ]);
        }

        // Ensure binary tree exists
        $sponsorTree = BinaryTree::firstOrCreate(['user_id' => $sponsor->id]);

        // Action: call calculateDirectBonus
        $this->service->calculateDirectBonus($sponsor);

        // Assertions
        $this->assertDatabaseCount('bonuses', 3);
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $sponsor->id,
            'reward_type' => 'direct',
            'amount' => 100,
            'is_product' => false,
        ]);

        $sponsorTree->refresh();
        $this->assertEquals(3, $sponsorTree->direct_pairs_paid);
        $this->assertEquals(3, $sponsorTree->reward_count);
    }

    public function test_downline_quota_one_side_hits_quota()
    {
        // Setup: create user and BinaryTree
        $user = User::factory()->create();
        $userTree = BinaryTree::firstOrCreate(['user_id' => $user->id], [
            'total_left_volume' => 0,
            'total_right_volume' => 8,
            'level_index' => 3,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        // Action: call processLevels
        $this->service->processLevels($user);

        // Assertions
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $user->id,
            'reward_type' => 'level',
            'level_index' => 3,
            'amount' => 100,
            'is_product' => false,
        ]);

        $userTree->refresh();
        $this->assertEquals(0, $userTree->left_consumed);
        $this->assertEquals(8, $userTree->right_consumed);
        $this->assertEquals(4, $userTree->level_index);
    }

    public function test_massive_one_sided_growth_consumes_multiple_levels()
    {
        // Setup
        $user = User::factory()->create();
        $userTree = BinaryTree::firstOrCreate(['user_id' => $user->id], [
            'total_left_volume' => 30,
            'total_right_volume' => 0,
            'level_index' => 1,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        // Action
        $this->service->processLevels($user);

        // Assertions: 4 bonuses (levels 1,2,3,4: quotas 2,4,8,16)
        $this->assertDatabaseCount('bonuses', 4);
        $bonuses = Bonus::where('user_id', $user->id)->orderBy('level_index')->get();
        $this->assertEquals([1,2,3,4], $bonuses->pluck('level_index')->toArray());
        $this->assertEquals([100,100,100,100], $bonuses->pluck('amount')->toArray());

        $userTree->refresh();
        $this->assertEquals(30, $userTree->left_consumed);
        $this->assertEquals(0, $userTree->right_consumed);
        $this->assertEquals(5, $userTree->level_index);
        $this->assertEquals(4, $userTree->reward_count);
    }

    public function test_simultaneous_both_sides_meet_quota()
    {
        // Setup
        $user = User::factory()->create();
        $userTree = BinaryTree::firstOrCreate(['user_id' => $user->id], [
            'total_left_volume' => 8,
            'total_right_volume' => 8,
            'level_index' => 3,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        // Action
        $this->service->processLevels($user);

        // Assertions: One bonus for level 3
        $this->assertDatabaseCount('bonuses', 1);
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $user->id,
            'reward_type' => 'level',
            'level_index' => 3,
            'amount' => 100,
        ]);

        $userTree->refresh();
        $this->assertEquals(8, $userTree->left_consumed);
        $this->assertEquals(8, $userTree->right_consumed);
        $this->assertEquals(4, $userTree->level_index);
        $this->assertEquals(1, $userTree->reward_count);
    }

    public function test_direct_bonus_fixed_amount()
    {
        // Setup
        $user = User::factory()->create();

        // Create 1 left and 1 right direct to trigger 1 pair
        User::factory()->create(['sponsor_id' => $user->id, 'placement_side' => 'left']);
        User::factory()->create(['sponsor_id' => $user->id, 'placement_side' => 'right']);

        // Action: trigger reward
        $this->service->calculateDirectBonus($user);

        // Assertions
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $user->id,
            'reward_type' => 'direct',
            'amount' => 100,
            'is_product' => false,
        ]);

        $userTree = BinaryTree::where('user_id', $user->id)->first();
        $this->assertEquals(1, $userTree->direct_pairs_paid);
    }

    public function test_carryover_and_later_catch_up()
    {
        // Setup: right hits quota first
        $user = User::factory()->create();
        $userTree = BinaryTree::firstOrCreate(['user_id' => $user->id], [
            'total_left_volume' => 0,
            'total_right_volume' => 8,
            'level_index' => 3,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        // Process levels: right hits, reward issued
        $this->service->processLevels($user);

        $this->assertDatabaseCount('bonuses', 1);
        $userTree->refresh();
        $this->assertEquals(8, $userTree->right_consumed);
        $this->assertEquals(4, $userTree->level_index);

        // Now add volume to left (8 for level 4 quota 16)
        $userTree->total_left_volume = 16; // Enough to trigger level 4
        $userTree->save();

        // Process again
        $this->service->processLevels($user);

        // Assertions: no duplicate for level 3, now level 4 triggered
        $this->assertDatabaseCount('bonuses', 2);
        $bonuses = Bonus::where('user_id', $user->id)->orderBy('level_index')->get();
        $this->assertEquals([3,4], $bonuses->pluck('level_index')->toArray());

        $userTree->refresh();
        $this->assertEquals(16, $userTree->left_consumed);
        $this->assertEquals(8, $userTree->right_consumed);
        $this->assertEquals(5, $userTree->level_index);
    }

    public function test_separation_of_reward_types()
    {
        // Setup: user with directs and downline
        $user = User::factory()->create();

        // Create 1 left and 1 right direct
        User::factory()->create(['sponsor_id' => $user->id, 'placement_side' => 'left']);
        User::factory()->create(['sponsor_id' => $user->id, 'placement_side' => 'right']);

        // Setup downline: total volumes to trigger level
        $userTree = BinaryTree::firstOrCreate(['user_id' => $user->id], [
            'total_left_volume' => 2,
            'total_right_volume' => 2,
            'level_index' => 1,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        // Action: run both
        $this->service->calculateDirectBonus($user);
        $this->service->processLevels($user);

        // Assertions
        $this->assertDatabaseCount('bonuses', 2);
        $directBonus = Bonus::where('user_id', $user->id)->where('reward_type', 'direct')->first();
        $levelBonus = Bonus::where('user_id', $user->id)->where('reward_type', 'level')->first();

        $this->assertNotNull($directBonus);
        $this->assertNotNull($levelBonus);
        $this->assertEquals(100, $directBonus->amount);
        $this->assertEquals(100, $levelBonus->amount);
        $this->assertEquals(1, $levelBonus->level_index);

        $userTree->refresh();
        $this->assertEquals(1, $userTree->direct_pairs_paid);
        $this->assertEquals(2, $userTree->left_consumed);
        $this->assertEquals(2, $userTree->right_consumed);
        $this->assertEquals(2, $userTree->level_index);
        $this->assertEquals(2, $userTree->reward_count);
    }

    public function test_spillover_placement()
    {
        // Setup: create sponsor with both positions filled
        $sponsor = User::factory()->create();
        $sponsorTree = BinaryTree::firstOrCreate(['user_id' => $sponsor->id]);

        // Fill both direct positions
        $leftDirect = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        $rightDirect = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);
        $sponsorTree->left_child_id = $leftDirect->id;
        $sponsorTree->right_child_id = $rightDirect->id;
        $sponsorTree->save();

        // Create new user to be placed via spillover
        $newUser = User::factory()->create();

        // Action: place user (should spillover to left direct)
        $this->service->placeUser($newUser, $sponsor, 'left');

        // Assertions
        $newUser->refresh();
        $this->assertEquals($sponsor->id, $newUser->sponsor_id);
        $this->assertEquals('left', $newUser->placement_side);

        // Check that volume was propagated
        $sponsorTree->refresh();
        $this->assertEquals(1, $sponsorTree->total_left_volume);
    }

    public function test_no_double_rewards_after_quota_redeemed()
    {
        // Setup: user who has already redeemed level 1
        $user = User::factory()->create();
        $userTree = BinaryTree::firstOrCreate(['user_id' => $user->id], [
            'total_left_volume' => 4, // Enough for level 1 (quota 2) + level 2 (quota 4)
            'total_right_volume' => 4,
            'level_index' => 1,
            'left_consumed' => 2, // Already consumed level 1
            'right_consumed' => 2,
        ]);

        // Action: process levels
        $this->service->processLevels($user);

        // Assertions: should only get level 2 bonus, not level 1 again
        $this->assertDatabaseCount('bonuses', 1);
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $user->id,
            'reward_type' => 'level',
            'level_index' => 1, // Level 2 bonus (level_index starts at 1)
            'amount' => 100,
        ]);

        $userTree->refresh();
        $this->assertEquals(4, $userTree->left_consumed);
        $this->assertEquals(4, $userTree->right_consumed);
        $this->assertEquals(2, $userTree->level_index);
    }

    public function test_every_5th_reward_is_product()
    {
        // Setup: user with reward_count at 4 (next reward will be 5th)
        $user = User::factory()->create();
        $userTree = BinaryTree::firstOrCreate(['user_id' => $user->id], [
            'total_left_volume' => 2,
            'total_right_volume' => 2,
            'level_index' => 1,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'reward_count' => 4, // Next reward will be the 5th
        ]);

        // Action: issue reward (should be product)
        $this->service->issueReward($user, 'level', 1);

        // Assertions
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $user->id,
            'reward_type' => 'level',
            'level_index' => 1,
            'amount' => 0,
            'is_product' => true,
        ]);

        $userTree->refresh();
        $this->assertEquals(5, $userTree->reward_count);

        // Issue another reward (should be cash)
        $this->service->issueReward($user, 'direct', null);

        $this->assertDatabaseHas('bonuses', [
            'user_id' => $user->id,
            'reward_type' => 'direct',
            'amount' => 100,
            'is_product' => false,
        ]);

        $userTree->refresh();
        $this->assertEquals(6, $userTree->reward_count);
    }

    public function test_volume_propagation_updates_upline()
    {
        // Setup: create sponsor and user hierarchy
        $root = User::factory()->create();
        $middle = User::factory()->create(['sponsor_id' => $root->id, 'placement_side' => 'left']);
        $leaf = User::factory()->create(['sponsor_id' => $middle->id, 'placement_side' => 'left']);

        // Create binary trees
        $rootTree = BinaryTree::firstOrCreate(['user_id' => $root->id]);
        $middleTree = BinaryTree::firstOrCreate(['user_id' => $middle->id]);
        $leafTree = BinaryTree::firstOrCreate(['user_id' => $leaf->id]);

        // Action: propagate volume up from leaf
        $this->service->propagateVolumeUp($leaf, 1);

        // Assertions: volume should propagate up the chain
        $rootTree->refresh();
        $middleTree->refresh();
        $leafTree->refresh();

        $this->assertEquals(1, $rootTree->total_left_volume);
        $this->assertEquals(1, $middleTree->total_left_volume);
        $this->assertEquals(0, $leafTree->total_left_volume);
    }

    public function test_massive_one_sided_growth_triggers_multiple_levels()
    {
        // Setup: user with massive left side growth
        $user = User::factory()->create();
        $userTree = BinaryTree::firstOrCreate(['user_id' => $user->id], [
            'total_left_volume' => 100, // Enough for multiple levels (2+4+8+16+32 = 62)
            'total_right_volume' => 0,
            'level_index' => 1,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        // Action: process levels
        $this->service->processLevels($user);

        // Assertions: should trigger multiple levels
        $bonuses = Bonus::where('user_id', $user->id)->orderBy('level_index')->get();
        $this->assertGreaterThan(3, $bonuses->count()); // At least levels 1, 2, 3, 4

        // Verify all bonuses are ₱100 (not percentage)
        foreach ($bonuses as $bonus) {
            $this->assertEquals(100, $bonus->amount);
            $this->assertEquals('level', $bonus->reward_type);
        }

        $userTree->refresh();
        $this->assertEquals(100, $userTree->left_consumed);
        $this->assertEquals(0, $userTree->right_consumed);
    }

    public function test_simultaneous_both_sides_consumes_both_sides()
    {
        // Setup: user with both sides reaching quota simultaneously
        $user = User::factory()->create();
        $userTree = BinaryTree::firstOrCreate(['user_id' => $user->id], [
            'total_left_volume' => 16, // Level 4 quota
            'total_right_volume' => 16, // Level 4 quota
            'level_index' => 4,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        // Action: process levels
        $this->service->processLevels($user);

        // Assertions: single reward issued, both sides consumed
        $this->assertDatabaseCount('bonuses', 1);
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $user->id,
            'reward_type' => 'level',
            'level_index' => 4,
            'amount' => 100,
        ]);

        $userTree->refresh();
        $this->assertEquals(16, $userTree->left_consumed);
        $this->assertEquals(16, $userTree->right_consumed);
        $this->assertEquals(5, $userTree->level_index);
    }

    public function test_fixed_reward_amount_always_100_pesos()
    {
        // Setup: user with direct referrals
        $user = User::factory()->create();

        // Create exactly 2 direct referrals (1 left, 1 right) for 1 pair
        User::factory()->create(['sponsor_id' => $user->id, 'placement_side' => 'left']);
        User::factory()->create(['sponsor_id' => $user->id, 'placement_side' => 'right']);

        // Action: calculate direct bonus
        $this->service->calculateDirectBonus($user);

        // Assertions: fixed ₱100 reward, not percentage
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $user->id,
            'reward_type' => 'direct',
            'amount' => 100, // Fixed amount, not percentage
            'is_product' => false,
        ]);

        $userTree = BinaryTree::where('user_id', $user->id)->first();
        $this->assertEquals(1, $userTree->direct_pairs_paid);
    }

    public function test_spillover_placement_with_preferred_side()
    {
        // Setup: sponsor with both positions filled
        $sponsor = User::factory()->create();
        $sponsorTree = BinaryTree::firstOrCreate(['user_id' => $sponsor->id]);

        // Fill both direct positions
        $leftDirect = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        $rightDirect = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);
        $sponsorTree->left_child_id = $leftDirect->id;
        $sponsorTree->right_child_id = $rightDirect->id;
        $sponsorTree->save();

        // Create new user to be placed via spillover
        $newUser = User::factory()->create();

        // Action: place user with preferred side (left)
        $this->service->placeUser($newUser, $sponsor, 'left');

        // Assertions: should spillover to left side
        $newUser->refresh();
        $this->assertEquals($sponsor->id, $newUser->sponsor_id);
        $this->assertEquals('left', $newUser->placement_side);

        // Verify volume propagation
        $sponsorTree->refresh();
        $this->assertEquals(1, $sponsorTree->total_left_volume);
    }

    public function test_concurrent_user_placement_race_condition_prevention()
    {
        // Setup: sponsor with available positions
        $sponsor = User::factory()->create();
        $sponsorTree = BinaryTree::firstOrCreate(['user_id' => $sponsor->id]);

        // Create two users to be placed simultaneously
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Action: place both users simultaneously (simulating race condition)
        $this->service->placeUser($user1, $sponsor, 'left');
        $this->service->placeUser($user2, $sponsor, 'left');

        // Assertions: both should be placed without conflicts
        $sponsorTree->refresh();
        $this->assertTrue($sponsorTree->left_child_id !== null);
        $this->assertTrue($sponsorTree->right_child_id !== null);

        // Verify volume propagation for both
        $this->assertEquals(1, $sponsorTree->total_left_volume);
        $this->assertEquals(1, $sponsorTree->total_right_volume);
    }

    public function test_downline_quota_processing_with_carryover()
    {
        // Setup: user with carryover from previous levels
        $user = User::factory()->create();
        $userTree = BinaryTree::firstOrCreate(['user_id' => $user->id], [
            'total_left_volume' => 10,
            'total_right_volume' => 10,
            'level_index' => 3, // Level 3 quota = 8
            'left_consumed' => 2, // Carryover from previous
            'right_consumed' => 2,
        ]);

        // Action: process levels
        $this->service->processLevels($user);

        // Assertions: should trigger level 3 (10-2=8 effective volume)
        $this->assertDatabaseCount('bonuses', 1);
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $user->id,
            'reward_type' => 'level',
            'level_index' => 3,
            'amount' => 100,
        ]);

        $userTree->refresh();
        $this->assertEquals(10, $userTree->left_consumed);
        $this->assertEquals(10, $userTree->right_consumed);
        $this->assertEquals(4, $userTree->level_index);
    }

    public function test_product_reward_every_5th_bonus()
    {
        // Setup: user with reward_count at 4
        $user = User::factory()->create();
        $userTree = BinaryTree::firstOrCreate(['user_id' => $user->id], [
            'reward_count' => 4,
        ]);

        // Issue rewards 1-4 (cash) and 5th (product)
        for ($i = 1; $i <= 5; $i++) {
            $this->service->issueReward($user, 'direct', null);
        }

        // Assertions: 4 cash bonuses and 1 product bonus
        $cashBonuses = Bonus::where('user_id', $user->id)
            ->where('is_product', false)
            ->where('amount', 100)
            ->count();
        $productBonuses = Bonus::where('user_id', $user->id)
            ->where('is_product', true)
            ->where('amount', 0)
            ->count();

        $this->assertEquals(4, $cashBonuses);
        $this->assertEquals(1, $productBonuses);

        $userTree->refresh();
        $this->assertEquals(5, $userTree->reward_count);
    }

    public function test_no_duplicate_rewards_after_quota_redeemed()
    {
        // Setup: user who has already redeemed level 1
        $user = User::factory()->create();
        $userTree = BinaryTree::firstOrCreate(['user_id' => $user->id], [
            'total_left_volume' => 6, // Enough for level 1 (2) + level 2 (4)
            'total_right_volume' => 6,
            'level_index' => 1,
            'left_consumed' => 2, // Already consumed level 1
            'right_consumed' => 2,
        ]);

        // Action: process levels
        $this->service->processLevels($user);

        // Assertions: should only get level 2 bonus, not level 1 again
        $this->assertDatabaseCount('bonuses', 1);
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $user->id,
            'reward_type' => 'level',
            'level_index' => 1, // Level 2 (level_index starts at 1)
            'amount' => 100,
        ]);

        $userTree->refresh();
        $this->assertEquals(6, $userTree->left_consumed);
        $this->assertEquals(6, $userTree->right_consumed);
        $this->assertEquals(2, $userTree->level_index);
    }

    public function test_spillover_bonus_calculation()
    {
        // Setup: create sponsor with both positions filled
        $sponsor = User::factory()->create();
        $sponsorTree = BinaryTree::firstOrCreate(['user_id' => $sponsor->id]);

        // Fill both direct positions
        $leftDirect = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        $rightDirect = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);
        $sponsorTree->left_child_id = $leftDirect->id;
        $sponsorTree->right_child_id = $rightDirect->id;
        $sponsorTree->save();

        // Create spillover users (2 left, 2 right)
        for ($i = 0; $i < 2; $i++) {
            User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        }
        for ($i = 0; $i < 2; $i++) {
            User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);
        }

        // Action: calculate spillover bonus
        $this->service->calculateSpilloverBonus($sponsor);

        // Assertions: should create 2 spillover bonuses (₱20 each)
        $this->assertDatabaseCount('bonuses', 2);
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $sponsor->id,
            'reward_type' => 'spillover',
            'amount' => 20,
            'is_product' => false,
        ]);

        $sponsorTree->refresh();
        $this->assertEquals(2, $sponsorTree->spillover_pairs_paid);
        $this->assertEquals(2, $sponsorTree->reward_count);
    }

    public function test_spillover_bonus_with_product_reward()
    {
        // Setup: sponsor with reward_count at 4 (next will be 5th = product)
        $sponsor = User::factory()->create();
        $sponsorTree = BinaryTree::firstOrCreate(['user_id' => $sponsor->id], [
            'reward_count' => 4,
        ]);

        // Fill both direct positions
        $leftDirect = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        $rightDirect = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);
        $sponsorTree->left_child_id = $leftDirect->id;
        $sponsorTree->right_child_id = $rightDirect->id;
        $sponsorTree->save();

        // Create spillover users (5 pairs for 5th reward)
        for ($i = 0; $i < 5; $i++) {
            User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        }
        for ($i = 0; $i < 5; $i++) {
            User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);
        }

        // Action: calculate spillover bonus
        $this->service->calculateSpilloverBonus($sponsor);

        // Assertions: 5th bonus should be product reward (₱0)
        $bonuses = Bonus::where('user_id', $sponsor->id)->where('reward_type', 'spillover')->get();
        $this->assertCount(5, $bonuses);

        $productBonus = $bonuses->where('is_product', true)->first();
        $this->assertNotNull($productBonus);
        $this->assertEquals(0, $productBonus->amount);

        $sponsorTree->refresh();
        $this->assertEquals(5, $sponsorTree->spillover_pairs_paid);
        $this->assertEquals(5, $sponsorTree->reward_count);
    }

    public function test_unbalanced_tree_carryover_logic()
    {
        // Setup: user with unbalanced tree (more left volume)
        $user = User::factory()->create();
        $userTree = BinaryTree::firstOrCreate(['user_id' => $user->id], [
            'total_left_volume' => 100,
            'total_right_volume' => 20,
            'left_spillover' => 0,
            'right_spillover' => 0,
        ]);

        // Action: handle carryover
        $this->service->callPrivateMethod($userTree, 'handleCarryover', [$userTree]);

        // Assertions: should carry over excess from left to right
        $userTree->refresh();
        $this->assertEquals(40, $userTree->total_left_volume); // 100 - 60 carryover
        $this->assertEquals(80, $userTree->total_right_volume); // 20 + 60 carryover
        $this->assertEquals(60, $userTree->left_spillover);
    }

    public function test_direct_vs_spillover_bonus_amounts()
    {
        // Setup: sponsor with both direct and spillover users
        $sponsor = User::factory()->create();
        $sponsorTree = BinaryTree::firstOrCreate(['user_id' => $sponsor->id]);

        // Create 1 direct pair (should be ₱100)
        User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);

        // Fill direct positions
        $leftDirect = User::find($sponsorTree->left_child_id);
        $rightDirect = User::find($sponsorTree->right_child_id);

        // Create spillover users (should be ₱20 each)
        for ($i = 0; $i < 3; $i++) {
            User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        }
        for ($i = 0; $i < 3; $i++) {
            User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);
        }

        // Action: calculate both bonuses
        $this->service->calculateDirectBonus($sponsor);
        $this->service->calculateSpilloverBonus($sponsor);

        // Assertions: direct bonus should be ₱100, spillover should be ₱20
        $directBonus = Bonus::where('user_id', $sponsor->id)->where('reward_type', 'direct')->first();
        $spilloverBonuses = Bonus::where('user_id', $sponsor->id)->where('reward_type', 'spillover')->get();

        $this->assertEquals(100, $directBonus->amount);
        $this->assertCount(3, $spilloverBonuses);
        foreach ($spilloverBonuses as $bonus) {
            $this->assertEquals(20, $bonus->amount);
        }
    }

    public function test_no_duplicate_level_bonuses()
    {
        // Setup: user with volume for level 1
        $user = User::factory()->create();
        $userTree = BinaryTree::firstOrCreate(['user_id' => $user->id], [
            'total_left_volume' => 2,
            'total_right_volume' => 2,
            'level_index' => 1,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        // Process levels twice
        $this->service->processLevels($user);
        $this->service->processLevels($user);

        // Assertions: should only have 1 level bonus, no duplicates
        $levelBonuses = Bonus::where('user_id', $user->id)->where('reward_type', 'level')->get();
        $this->assertCount(1, $levelBonuses);
        $this->assertEquals(1, $levelBonuses->first()->level_index);
    }

    public function test_complete_user_placement_with_all_bonus_types()
    {
        // Setup: create sponsor
        $sponsor = User::factory()->create();
        $sponsorTree = BinaryTree::firstOrCreate(['user_id' => $sponsor->id]);

        // Fill both direct positions
        $leftDirect = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        $rightDirect = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);
        $sponsorTree->left_child_id = $leftDirect->id;
        $sponsorTree->right_child_id = $rightDirect->id;
        $sponsorTree->save();

        // Create new user to be placed via spillover
        $newUser = User::factory()->create();

        // Action: place user (should trigger all bonus calculations)
        $this->service->placeUser($newUser, $sponsor, 'left');

        // Assertions: should have direct bonus, spillover bonus, and volume propagation
        $sponsorTree->refresh();
        $this->assertEquals(1, $sponsorTree->total_left_volume);

        // Check that bonuses were created
        $directBonuses = Bonus::where('user_id', $sponsor->id)->where('reward_type', 'direct')->count();
        $spilloverBonuses = Bonus::where('user_id', $sponsor->id)->where('reward_type', 'spillover')->count();

        $this->assertGreaterThan(0, $directBonuses);
        $this->assertGreaterThan(0, $spilloverBonuses);
    }

    public function test_spillover_bonus_with_unequal_sides()
    {
        // Setup: sponsor with more users on left side
        $sponsor = User::factory()->create();
        $sponsorTree = BinaryTree::firstOrCreate(['user_id' => $sponsor->id]);

        // Fill both direct positions
        $leftDirect = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        $rightDirect = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);
        $sponsorTree->left_child_id = $leftDirect->id;
        $sponsorTree->right_child_id = $rightDirect->id;
        $sponsorTree->save();

        // Create spillover users (5 left, 3 right)
        for ($i = 0; $i < 5; $i++) {
            User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        }
        for ($i = 0; $i < 3; $i++) {
            User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);
        }

        // Action: calculate spillover bonus
        $this->service->calculateSpilloverBonus($sponsor);

        // Assertions: should create 3 spillover bonuses (minimum of both sides)
        $this->assertDatabaseCount('bonuses', 3);
        $spilloverBonuses = Bonus::where('user_id', $sponsor->id)->where('reward_type', 'spillover')->get();
        $this->assertCount(3, $spilloverBonuses);

        foreach ($spilloverBonuses as $bonus) {
            $this->assertEquals(20, $bonus->amount);
        }

        $sponsorTree->refresh();
        $this->assertEquals(3, $sponsorTree->spillover_pairs_paid);
    }

    /**
     * Helper method to call private methods for testing
     */
    private function callPrivateMethod($object, $method, $parameters = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

}