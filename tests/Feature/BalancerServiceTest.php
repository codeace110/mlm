<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BinaryTree;
use App\Models\Earning;
use App\Models\BonusSettings;
use App\Services\BalancerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalancerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create bonus settings for testing
        BonusSettings::create([
            'package_value' => 100,
            'pair_bonus_amount' => 10,
            'balancer_ratio' => '1:1',
            'direct_bonus_percent' => 5,
            'matching_bonus_percent' => 2,
        ]);
    }

    /** @test */
    public function direct_pairs_pay_100_percent()
    {
        // Create a user with balanced volumes (3 left, 3 right)
        $user = User::factory()->create();
        $tree = BinaryTree::create([
            'user_id' => $user->id,
            'left_volume' => 3,
            'right_volume' => 3,
            'left_spillover' => 0,
            'right_spillover' => 0,
        ]);

        $balancer = new BalancerService();
        $balancer->processPairs($user);

        // Should create 3 pairs at 100% = 30 total bonus
        $this->assertDatabaseHas('earnings', [
            'user_id' => $user->id,
            'amount' => 30, // 3 pairs * 10 bonus each
            'type' => 'pair',
            'description' => 'Direct pair bonus: 3 pairs at 100%',
        ]);

        // Volumes should be consumed
        $tree->refresh();
        $this->assertEquals(0, $tree->left_volume);
        $this->assertEquals(0, $tree->right_volume);
        $this->assertEquals(0, $tree->carryover_left);
        $this->assertEquals(0, $tree->carryover_right);
    }

    /** @test */
    public function unbalanced_direct_pairs_leave_remainder()
    {
        // Create a user with unbalanced volumes (5 left, 8 right)
        $user = User::factory()->create();
        $tree = BinaryTree::create([
            'user_id' => $user->id,
            'left_volume' => 5,
            'right_volume' => 8,
            'left_spillover' => 0,
            'right_spillover' => 0,
        ]);

        $balancer = new BalancerService();
        $balancer->processPairs($user);

        // Should create 5 pairs at 100% = 50 total bonus
        $this->assertDatabaseHas('earnings', [
            'user_id' => $user->id,
            'amount' => 50, // 5 pairs * 10 bonus each
            'type' => 'pair',
            'description' => 'Direct pair bonus: 5 pairs at 100%',
        ]);

        // 5 left consumed, 3 right remaining
        $tree->refresh();
        $this->assertEquals(0, $tree->left_volume);
        $this->assertEquals(3, $tree->right_volume);
        $this->assertEquals(0, $tree->carryover_left);
        $this->assertEquals(3, $tree->carryover_right);
    }

    /** @test */
    public function spillover_pairs_pay_20_percent()
    {
        // Create a user with spillover volumes (3 left spillover, 3 right spillover)
        $user = User::factory()->create();
        $tree = BinaryTree::create([
            'user_id' => $user->id,
            'left_volume' => 0,
            'right_volume' => 0,
            'left_spillover' => 3,
            'right_spillover' => 3,
        ]);

        $balancer = new BalancerService();
        $balancer->processPairs($user);

        // Should create 3 spillover pairs at 20% = 6 total bonus (3 pairs * 10 * 0.20)
        $this->assertDatabaseHas('earnings', [
            'user_id' => $user->id,
            'amount' => 6, // 3 pairs * 10 bonus * 0.20
            'type' => 'spillover',
            'description' => 'Spillover pair bonus: 3 pairs at 20%',
        ]);

        // Spillover volumes should be consumed
        $tree->refresh();
        $this->assertEquals(0, $tree->left_spillover);
        $this->assertEquals(0, $tree->right_spillover);
        $this->assertEquals(0, $tree->carryover_left);
        $this->assertEquals(0, $tree->carryover_right);
    }

    /** @test */
    public function carryover_combines_remaining_volumes()
    {
        // Create a user with mixed volumes
        $user = User::factory()->create();
        $tree = BinaryTree::create([
            'user_id' => $user->id,
            'left_volume' => 2,
            'right_volume' => 4,
            'left_spillover' => 1,
            'right_spillover' => 2,
        ]);

        $balancer = new BalancerService();
        $balancer->processPairs($user);

        // Should create 2 direct pairs (2 left + 2 right consumed)
        $this->assertDatabaseHas('earnings', [
            'user_id' => $user->id,
            'amount' => 20, // 2 pairs * 10 bonus
            'type' => 'pair',
        ]);

        // Should create 1 spillover pair (1 left spillover + 1 right spillover consumed)
        $this->assertDatabaseHas('earnings', [
            'user_id' => $user->id,
            'amount' => 2, // 1 pair * 10 * 0.20
            'type' => 'spillover',
        ]);

        // Carryover should be remaining volumes
        $tree->refresh();
        $this->assertEquals(0, $tree->left_volume);    // consumed
        $this->assertEquals(2, $tree->right_volume);   // 4-2=2 remaining
        $this->assertEquals(0, $tree->left_spillover); // consumed
        $this->assertEquals(1, $tree->right_spillover); // 2-1=1 remaining
        $this->assertEquals(0, $tree->carryover_left);  // 0 + 0
        $this->assertEquals(3, $tree->carryover_right); // 2 + 1
    }

    /** @test */
    public function matching_bonuses_are_awarded_to_uplines()
    {
        // Create upline user
        $upline = User::factory()->create();
        BinaryTree::create(['user_id' => $upline->id]);

        // Create downline user
        $user = User::factory()->create(['sponsor_id' => $upline->id]);
        $tree = BinaryTree::create([
            'user_id' => $user->id,
            'left_volume' => 2,
            'right_volume' => 2,
        ]);

        $balancer = new BalancerService();
        $balancer->processPairs($user);

        // Should have direct pair bonus for downline
        $this->assertDatabaseHas('earnings', [
            'user_id' => $user->id,
            'amount' => 20, // 2 pairs * 10
            'type' => 'pair',
        ]);

        // Should have matching bonus for upline (2% of 20 = 0.4)
        $this->assertDatabaseHas('earnings', [
            'user_id' => $upline->id,
            'amount' => 0.4,
            'type' => 'matching',
        ]);
    }
}
