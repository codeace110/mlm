<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AdminCode;
use App\Models\BinaryTree;
use App\Models\Bonus;
use App\Models\BonusSettings;
use App\Models\BonusRule;
use App\Services\AdminCodeService;
use App\Services\BinaryTreeService;
use App\Services\BinaryBalancerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteMlmSystemIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function complete_mlm_workflow_admin_generates_code_user_registers_and_gets_bonuses()
    {
        // 1. Create admin and distributor
        $admin = User::factory()->create(['is_admin' => true]);
        $distributor = User::factory()->create();

        $this->actingAs($admin);

        // 2. Generate admin codes using the service
        $adminCodeService = new AdminCodeService();
        $codes = $adminCodeService->generateBatch($distributor, 'Test Batch', 15);

        $this->assertCount(15, $codes);
        $this->assertDatabaseHas('admin_codes', [
            'batch_name' => 'Test Batch',
            'distributor_id' => $distributor->id,
            'status' => 'unused'
        ]);

        // 3. Pick one code for user registration
        $registrationCode = $codes[0];

        // 4. User registers with the code
        $newUser = User::factory()->create([
            'email' => 'newuser@example.com',
            'password' => bcrypt('password'),
            'registration_code' => $registrationCode
        ]);

        // 5. Validate and use the code
        $sponsor = $adminCodeService->validateAndUseCode($registrationCode, $newUser);

        $this->assertEquals($distributor->id, $sponsor->id);
        $this->assertDatabaseHas('admin_codes', [
            'code' => $registrationCode,
            'status' => 'used',
            'used_by_user_id' => $newUser->id
        ]);

        // 6. Check if user was placed in binary tree
        $this->assertDatabaseHas('binary_trees', [
            'user_id' => $newUser->id
        ]);

        // 7. Check if volume was propagated to sponsor
        $distributorTree = BinaryTree::where('user_id', $distributor->id)->first();
        $this->assertEquals(1, $distributorTree->total_left_volume);

        // 8. Create bonus settings and rules
        BonusSettings::create([
            'package_value' => 1000,
            'direct_bonus_percent' => 10,
            'pair_bonus_amount' => 100,
            'balancer_ratio' => 1.0,
            'matching_bonus_percent' => 5,
        ]);

        BonusRule::create([
            'name' => 'Test Product Rule',
            'type' => 'product_reward',
            'percentage' => 5,
            'min_amount' => 0,
            'max_amount' => 10000,
            'is_active' => true,
        ]);

        // 9. Process bonuses using the balancer service
        $balancerService = new BinaryBalancerService();
        $balancerService->processLevels($distributor);

        // 10. Check if bonuses were created
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $distributor->id,
            'reward_type' => 'level'
        ]);

        echo "\n✓ Complete MLM workflow test passed!\n";
        echo "✓ Admin code generation: SUCCESS\n";
        echo "✓ User registration with code: SUCCESS\n";
        echo "✓ Binary tree placement: SUCCESS\n";
        echo "✓ Volume propagation: SUCCESS\n";
        echo "✓ Bonus calculation: SUCCESS\n";
    }

    /** @test */
    public function binary_tree_spillover_and_bonus_calculation()
    {
        // Create sponsor without binary tree (will be created by service)
        $sponsor = User::factory()->create();

        // Create bonus settings
        BonusSettings::create([
            'package_value' => 1000,
            'direct_bonus_percent' => 10,
            'pair_bonus_amount' => 100,
            'balancer_ratio' => 1.0,
            'matching_bonus_percent' => 5,
        ]);

        // Place multiple users to test spillover
        $users = [];
        for ($i = 0; $i < 5; $i++) {
            $user = User::factory()->create(['sponsor_id' => $sponsor->id]);
            $users[] = $user;

            // Manually place in binary tree
            $binaryTreeService = new BinaryTreeService();
            $binaryTreeService->placeUserInTree($user, $sponsor);
        }

        // Check spillover occurred
        $sponsorTree = BinaryTree::where('user_id', $sponsor->id)->first();
        $sponsorTree->refresh();
        $this->assertGreaterThan(2, $sponsorTree->total_left_volume + $sponsorTree->total_right_volume);

        // Process bonuses
        $balancerService = new BinaryBalancerService();
        $balancerService->processLevels($sponsor);

        // Check if level bonuses were created
        $bonuses = Bonus::where('user_id', $sponsor->id)->where('reward_type', 'level')->get();
        $this->assertGreaterThan(0, $bonuses->count());

        echo "\n✓ Binary tree spillover test passed!\n";
        echo "✓ Multiple user placement: SUCCESS\n";
        echo "✓ Spillover logic: SUCCESS\n";
        echo "✓ Level bonus calculation: SUCCESS\n";
    }

    /** @test */
    public function bonus_rules_and_settings_integration()
    {
        // Create bonus settings
        $bonusSettings = BonusSettings::create([
            'package_value' => 2000,
            'direct_bonus_percent' => 15,
            'pair_bonus_amount' => 150,
            'balancer_ratio' => 1.0,
            'matching_bonus_percent' => 5,
        ]);

        // Create bonus rule
        $bonusRule = BonusRule::create([
            'name' => 'Custom Product Rule',
            'type' => 'product_reward',
            'percentage' => 3, // Every 3rd reward
            'min_amount' => 0,
            'max_amount' => 5000,
            'is_active' => true,
        ]);

        // Test that services use these settings
        $binaryTreeService = new BinaryTreeService();
        $balancerService = new BinaryBalancerService();

        // The services should now use the configured values
        $this->assertEquals(300, $binaryTreeService->getDirectBonusAmount()); // 15% of 2000
        $this->assertEquals(3, $balancerService->getProductRewardInterval());

        echo "\n✓ Bonus rules integration test passed!\n";
        echo "✓ Bonus settings configuration: SUCCESS\n";
        echo "✓ Bonus rules configuration: SUCCESS\n";
        echo "✓ Service integration: SUCCESS\n";
    }
}