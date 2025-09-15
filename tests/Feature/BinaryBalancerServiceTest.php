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

    public function test_admin_code_registration_triggers_placement_and_propagation()
    {
        // Setup: create distributor and admin code
        $distributor = User::factory()->create();
        $adminCode = \App\Models\AdminCode::create([
            'code' => 'TESTCODE123',
            'distributor_id' => $distributor->id,
            'status' => 'issued',
        ]);

        // Create first direct to form a pair
        $firstDirect = User::factory()->create(['sponsor_id' => $distributor->id, 'placement_side' => 'left']);
        $this->service->placeUser($firstDirect, $distributor, 'left');

        // Simulate registration with admin code
        $newUser = User::factory()->create([
            'sponsor_id' => $distributor->id,
        ]);

        // Use the admin code
        $adminCodeService = new \App\Services\AdminCodeService();
        $adminCodeService->validateAndUseCode('TESTCODE123', $newUser);

        // Place user (right side to form pair)
        $this->service->placeUser($newUser, $distributor, 'right');

        // Assertions: admin code used
        $adminCode->refresh();
        $this->assertEquals('used', $adminCode->status);
        $this->assertEquals($newUser->id, $adminCode->used_by_user_id);
        $this->assertNotNull($adminCode->used_at);

        // Volume propagated
        $distributorTree = \App\Models\BinaryTree::where('user_id', $distributor->id)->first();
        $this->assertEquals(1, $distributorTree->total_left_volume);
        $this->assertEquals(1, $distributorTree->total_right_volume);

        // Bonuses calculated (direct pair)
        $this->assertDatabaseCount('bonuses', 1);
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $distributor->id,
            'reward_type' => 'direct',
            'amount' => 100,
        ]);
    }
}