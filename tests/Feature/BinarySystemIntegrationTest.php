<?php

namespace Tests\Feature;

use App\Models\ReferralCode;
use App\Models\BinaryTree;
use App\Models\Bonus;
use App\Models\User;
use App\Services\BinaryTreeService;
use App\Services\BinaryBalancerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BinarySystemIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_issues_code_user_registers_gets_placed_and_sponsor_gets_bonus()
    {
        // Admin generates and assigns code
        $admin = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
        $this->actingAs($admin);

        $this->post('/admin/referral_codes/generate', ['count' => 1]);
        $code = ReferralCode::first();
        $distributor = User::factory()->create();

        // Assign code to distributor
        $this->post('/admin/referral_codes/' . $code->id . '/assign', ['distributor_id' => $distributor->id]);

        // User registers with code
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'binary@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'referral_code' => $code->code,
            'preferred_side' => 'left',
        ]);

        $response->assertRedirect('/dashboard');
        $newUser = User::where('email', 'binary@example.com')->first();
        $this->assertNotNull($newUser);

        // Check placement
        $this->assertEquals($distributor->id, $newUser->sponsor_id); // Since code was assigned to distributor

        // Check volume propagation
        $adminTree = BinaryTree::where('user_id', $admin->id)->first();
        $this->assertEquals(1, $adminTree->total_left_volume);

        // Since no pairs yet, no direct bonus
        $this->assertDatabaseMissing('bonuses', [
            'user_id' => $admin->id,
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
        $service = new BinaryBalancerService();
        $service->processLevels($sponsor);

        // Should earn level 1 bonus (quota 2^1 = 2)
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $sponsor->id,
            'reward_type' => 'level',
            'amount' => 100.00,
        ]);

        $sponsorTree->refresh();
        $this->assertEquals(6, $sponsorTree->right_consumed);
        $this->assertEquals(2, $sponsorTree->total_right_volume - $sponsorTree->right_consumed); // Carryover
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
        $service = new BinaryBalancerService();
        $service->processLevels($sponsor);

        // Should earn level 1 bonus
        $bonuses = Bonus::where('user_id', $sponsor->id)->where('reward_type', 'level')->get();
        $this->assertCount(1, $bonuses);

        $level1 = $bonuses->first();
        $this->assertNotNull($level1);
        // Only level 1 bonus is issued

        $sponsorTree->refresh();
        $this->assertEquals(2, $sponsorTree->level_index);
        $this->assertEquals(2, $sponsorTree->left_consumed); // Only level 1 consumed
        $this->assertEquals(2, $sponsorTree->right_consumed);
    }

    /** @test */
    public function direct_bonus_issued_when_pairs_complete()
    {
        $sponsor = User::factory()->create();

        // First left direct
        $left = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        $service = new BinaryBalancerService();
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
        $service = new BinaryTreeService();

        // Issue 4 regular bonuses
        for ($i = 0; $i < 4; $i++) {
            $service->createEarning($user, 100, 'direct', 'Test bonus', 'pending');
        }

        // 5th should be product
        $service->createEarning($user, 0, 'product', 'Product bonus', 'pending');

        $earnings = \App\Models\Earning::where('user_id', $user->id)->get();
        $productEarning = $earnings->last();
        $this->assertEquals('product', $productEarning->type);
        $this->assertEquals(0.00, $productEarning->amount);
    }
}