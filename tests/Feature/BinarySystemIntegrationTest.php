<?php

namespace Tests\Feature;

use App\Models\AdminCode;
use App\Models\BinaryTree;
use App\Models\Bonus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BinarySystemIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_issues_code_user_registers_gets_placed_and_sponsor_gets_bonus()
    {
        // Admin generates and assigns code
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $this->post('/admin/admin_codes/generate', ['count' => 1]);
        $code = AdminCode::first();
        $distributor = User::factory()->create();
        $this->post("/admin/admin_codes/{$code->id}/assign", [
            'distributor_id' => $distributor->id,
        ]);

        // User registers with code
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'admin_code' => $code->code,
            'preferred_side' => 'left',
        ]);

        $response->assertRedirect('/dashboard');
        $newUser = User::where('email', 'new@example.com')->first();
        $this->assertNotNull($newUser);

        // Check placement
        $this->assertEquals($distributor->id, $newUser->sponsor_id);
        $this->assertEquals('left', $newUser->placement_side);

        // Check volume propagation
        $distributorTree = BinaryTree::where('user_id', $distributor->id)->first();
        $this->assertEquals(1, $distributorTree->total_left_volume);

        // Since no pairs yet, no direct bonus
        $this->assertDatabaseMissing('bonuses', [
            'user_id' => $distributor->id,
            'reward_type' => 'direct',
        ]);
    }

    /** @test */
    public function one_sided_growth_earns_quota_bonus()
    {
        $sponsor = User::factory()->create();
        $sponsorTree = BinaryTree::factory()->create(['user_id' => $sponsor->id]);

        // Place 8 users on right
        for ($i = 0; $i < 8; $i++) {
            $user = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);
            // Simulate placement and propagation
            $sponsorTree->increment('total_right_volume');
            $sponsorTree->save();
        }

        // Process levels
        $service = new \App\Services\BinaryBalancerService();
        $service->processLevels($sponsor);

        // Should earn level 1 bonus (quota 2^1 = 2)
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $sponsor->id,
            'reward_type' => 'level',
            'level_index' => 1,
            'amount' => 100.00,
        ]);

        $sponsorTree->refresh();
        $this->assertEquals(2, $sponsorTree->right_consumed);
        $this->assertEquals(6, $sponsorTree->total_right_volume - $sponsorTree->right_consumed); // Carryover
    }

    /** @test */
    public function balanced_growth_earns_multiple_bonuses()
    {
        $sponsor = User::factory()->create();
        $sponsorTree = BinaryTree::factory()->create(['user_id' => $sponsor->id]);

        // Place 4 on left, 4 on right
        for ($i = 0; $i < 4; $i++) {
            User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
            User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);
            $sponsorTree->increment('total_left_volume');
            $sponsorTree->increment('total_right_volume');
        }
        $sponsorTree->save();

        // Process levels
        $service = new \App\Services\BinaryBalancerService();
        $service->processLevels($sponsor);

        // Should earn level 1 and level 2 bonuses
        $bonuses = Bonus::where('user_id', $sponsor->id)->where('reward_type', 'level')->get();
        $this->assertCount(2, $bonuses);

        $level1 = $bonuses->where('level_index', 1)->first();
        $level2 = $bonuses->where('level_index', 2)->first();
        $this->assertNotNull($level1);
        $this->assertNotNull($level2);

        $sponsorTree->refresh();
        $this->assertEquals(2, $sponsorTree->level_index);
        $this->assertEquals(6, $sponsorTree->left_consumed); // 2 + 4
        $this->assertEquals(6, $sponsorTree->right_consumed);
    }

    /** @test */
    public function direct_bonus_issued_when_pairs_complete()
    {
        $sponsor = User::factory()->create();

        // First left direct
        $left = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        $service = new \App\Services\BinaryBalancerService();
        $service->placeUser($left, $sponsor, 'left');

        // No bonus yet
        $this->assertDatabaseMissing('bonuses', [
            'user_id' => $sponsor->id,
            'reward_type' => 'direct',
        ]);

        // Second right direct
        $right = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);
        $service->placeUser($right, $sponsor, 'right');

        // Now bonus issued
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $sponsor->id,
            'reward_type' => 'direct',
            'amount' => 100.00,
        ]);
    }

    /** @test */
    public function product_reward_every_fifth_bonus()
    {
        $user = User::factory()->create();
        $service = new \App\Services\BinaryBalancerService();

        // Issue 4 regular bonuses
        for ($i = 0; $i < 4; $i++) {
            $service->issueReward($user, 'direct');
        }

        // 5th should be product
        $service->issueReward($user, 'direct');

        $bonuses = Bonus::where('user_id', $user->id)->get();
        $productBonus = $bonuses->last();
        $this->assertTrue($productBonus->is_product);
        $this->assertEquals(0.00, $productBonus->amount);
    }
}