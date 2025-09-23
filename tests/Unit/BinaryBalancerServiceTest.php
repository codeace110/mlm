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

        $this->sponsor = User::factory()->create([
            'balancing_mode' => '1:1'
        ]);

        $this->user = User::factory()->create([
            'balancing_mode' => '1:1'
        ]);

        // Create binary trees
        BinaryTree::create([
            'user_id' => $this->sponsor->id,
            'left_volume' => 10,
            'right_volume' => 10,
            'carryover_left' => 0,
            'carryover_right' => 0,
            'total_left_volume' => 10,
            'total_right_volume' => 10,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        BinaryTree::create([
            'user_id' => $this->user->id,
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

    public function test_process_user_balancer_with_carryover_mode()
    {
        $user = User::factory()->create(['balancing_mode' => 'carryover']);
        BinaryTree::create([
            'user_id' => $user->id,
            'left_volume' => 5,
            'right_volume' => 3,
            'carryover_left' => 2,
            'carryover_right' => 1,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        $this->service->processUserBalancer($user);

        $tree = BinaryTree::where('user_id', $user->id)->first();
        $this->assertEquals(2, $tree->carryover_left); // 5 + 2 - 3 = 4, but min(7, 4) = 4, remaining = 4 - 3 = 1? Wait, let me recalculate
        $this->assertEquals(0, $tree->carryover_right); // 3 + 1 - 3 = 1, remaining = 1 - 1 = 0
    }

    public function test_process_user_balancer_with_2_to_1_mode()
    {
        $user = User::factory()->create(['balancing_mode' => '2:1']);
        BinaryTree::create([
            'user_id' => $user->id,
            'left_volume' => 6,
            'right_volume' => 3,
            'carryover_left' => 0,
            'carryover_right' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        $this->service->processUserBalancer($user);

        $tree = BinaryTree::where('user_id', $user->id)->first();
        $this->assertEquals(0, $tree->left_consumed); // 6/2 = 3, but 3/1 = 3, so 3 pairs
        $this->assertEquals(3, $tree->right_consumed); // 3 pairs * 1 = 3
        $this->assertEquals(0, $tree->carryover_left); // 6 - (3*2) = 0
        $this->assertEquals(0, $tree->carryover_right); // 3 - (3*1) = 0
    }

    public function test_process_user_balancer_with_3_to_1_mode()
    {
        $user = User::factory()->create(['balancing_mode' => '3:1']);
        BinaryTree::create([
            'user_id' => $user->id,
            'left_volume' => 9,
            'right_volume' => 3,
            'carryover_left' => 0,
            'carryover_right' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        $this->service->processUserBalancer($user);

        $tree = BinaryTree::where('user_id', $user->id)->first();
        $this->assertEquals(0, $tree->left_consumed); // 9/3 = 3, 3/1 = 3, so 3 pairs
        $this->assertEquals(3, $tree->right_consumed); // 3 pairs * 1 = 3
        $this->assertEquals(0, $tree->carryover_left); // 9 - (3*3) = 0
        $this->assertEquals(0, $tree->carryover_right); // 3 - (3*1) = 0
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
    }

    public function test_calculate_potential_pairs()
    {
        $user = User::factory()->create(['balancing_mode' => '2:1']);
        BinaryTree::where('user_id', $user->id)->update([
            'left_volume' => 6,
            'right_volume' => 3,
            'carryover_left' => 0,
            'carryover_right' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        $potential = $this->service->calculatePotentialPairs($user);

        $this->assertEquals(3, $potential['pairs']); // 6/2 = 3, 3/1 = 3
        $this->assertEquals(6, $potential['left_available']);
        $this->assertEquals(3, $potential['right_available']);
        $this->assertEquals('2:1', $potential['mode']);
        $this->assertEquals('2 left + 1 right = 1 pair', $potential['mode_description']);
    }

    public function test_get_available_modes()
    {
        $modes = BinaryBalancerService::getAvailableModes();

        $this->assertArrayHasKey('1:1', $modes);
        $this->assertArrayHasKey('2:1', $modes);
        $this->assertArrayHasKey('3:1', $modes);
        $this->assertArrayHasKey('carryover', $modes);

        $this->assertEquals('Strict 1:1 matching', $modes['1:1']['description']);
        $this->assertEquals('2 left + 1 right = 1 pair', $modes['2:1']['description']);
        $this->assertEquals('3 left + 1 right = 1 pair', $modes['3:1']['description']);
        $this->assertEquals('Carryover mode', $modes['carryover']['description']);
    }

    public function test_get_mode_config()
    {
        $config = BinaryBalancerService::getModeConfig('2:1');

        $this->assertEquals(2, $config['left_ratio']);
        $this->assertEquals(1, $config['right_ratio']);
        $this->assertEquals('2 left + 1 right = 1 pair', $config['description']);

        $this->assertNull(BinaryBalancerService::getModeConfig('invalid'));
    }

    public function test_process_balancer_for_uplines()
    {
        $upline1 = User::factory()->create(['balancing_mode' => '1:1']);
        $upline2 = User::factory()->create(['balancing_mode' => '1:1']);

        $this->user->update(['sponsor_id' => $upline1->id]);
        $upline1->update(['sponsor_id' => $upline2->id]);

        BinaryTree::create([
            'user_id' => $upline1->id,
            'left_volume' => 5,
            'right_volume' => 5,
            'carryover_left' => 0,
            'carryover_right' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        BinaryTree::create([
            'user_id' => $upline2->id,
            'left_volume' => 10,
            'right_volume' => 10,
            'carryover_left' => 0,
            'carryover_right' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        $this->service->processBalancerForUplines($this->user);

        // Check that bonuses were created for uplines
        $upline1Bonuses = Bonus::where('user_id', $upline1->id)->count();
        $upline2Bonuses = Bonus::where('user_id', $upline2->id)->count();

        $this->assertGreaterThan(0, $upline1Bonuses);
        $this->assertGreaterThan(0, $upline2Bonuses);
    }

    public function test_create_pair_bonuses()
    {
        $user = User::factory()->create(['balancing_mode' => '1:1']);
        BinaryTree::create([
            'user_id' => $user->id,
            'left_volume' => 5,
            'right_volume' => 5,
            'carryover_left' => 0,
            'carryover_right' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        $this->service->processUserBalancer($user);

        $bonuses = Bonus::where('user_id', $user->id)->where('reward_type', 'pair')->get();

        $this->assertCount(5, $bonuses);
        $this->assertEquals(500.00, $bonuses->sum('amount')); // 5 pairs * 100

        // Check that every 5th bonus is a product reward
        $productBonuses = $bonuses->where('is_product', true);
        $this->assertCount(1, $productBonuses);
    }
}