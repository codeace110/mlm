<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AdminCode;
use App\Models\BinaryTree;
use App\Models\Bonus;
use App\Services\BinaryBalancerService;
use App\Services\EnhancedReferralCodeService;
use App\Services\GenealogyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * System Integration Test
 *
 * Tests the complete MLM system functionality after fixes
 */
class SystemIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected BinaryBalancerService $binaryService;
    protected EnhancedReferralCodeService $codeService;
    protected GenealogyService $genealogyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->binaryService = app(BinaryBalancerService::class);
        $this->codeService = app(EnhancedReferralCodeService::class);
        $this->genealogyService = app(GenealogyService::class);
    }

    /** @test */
    public function test_complete_user_registration_with_referral_code()
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Generate referral codes
        $codes = $this->codeService->generateBatch($admin, 5, 'Test Batch', 30);

        // Create sponsor
        $sponsor = User::factory()->create();
        $sponsorCode = AdminCode::where('code', $codes[0])->first();
        $sponsorCode->update(['distributor_id' => $sponsor->id, 'status' => 'available']);

        // Register new user with referral code
        $newUserData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => $codes[0],
            'preferred_side' => 'left',
        ];

        $response = $this->post(route('register'), $newUserData);

        // Assert successful registration
        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'sponsor_id' => $sponsor->id,
            'placement_side' => 'left',
        ]);

        // Assert code was marked as used
        $this->assertDatabaseHas('admin_codes', [
            'code' => $codes[0],
            'status' => 'used',
            'used_by_user_id' => User::where('email', 'newuser@example.com')->first()->id,
        ]);

        // Assert binary tree was created
        $newUser = User::where('email', 'newuser@example.com')->first();
        $this->assertDatabaseHas('binary_trees', [
            'user_id' => $newUser->id,
        ]);
    }

    /** @test */
    public function test_binary_tree_placement_and_bonus_calculation()
    {
        // Create sponsor
        $sponsor = User::factory()->create();

        // Create binary tree for sponsor
        BinaryTree::create([
            'user_id' => $sponsor->id,
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 1,
            'reward_count' => 0,
            'direct_pairs_paid' => 0,
            'spillover_pairs_paid' => 0,
            'left_spillover' => 0,
            'right_spillover' => 0,
        ]);

        // Create first user (direct referral)
        $user1 = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'left']);
        $this->binaryService->placeUser($user1, $sponsor, 'left');

        // Create second user (direct referral)
        $user2 = User::factory()->create(['sponsor_id' => $sponsor->id, 'placement_side' => 'right']);
        $this->binaryService->placeUser($user2, $sponsor, 'right');

        // Assert direct bonus was created
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $sponsor->id,
            'reward_type' => 'direct',
            'amount' => 100.00,
        ]);

        // Create spillover users on both sides
        $spillover1 = User::factory()->create(['sponsor_id' => $sponsor->id]);
        $this->binaryService->placeUser($spillover1, $sponsor, 'left');

        $spillover2 = User::factory()->create(['sponsor_id' => $sponsor->id]);
        $this->binaryService->placeUser($spillover2, $sponsor, 'right');

        // Assert spillover bonus was created
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $sponsor->id,
            'reward_type' => 'spillover',
            'amount' => 100.00,
        ]);
    }

    /** @test */
    public function test_genealogy_service_functionality()
    {
        // Create user hierarchy
        $root = User::factory()->create();
        $leftChild = User::factory()->create(['sponsor_id' => $root->id, 'placement_side' => 'left']);
        $rightChild = User::factory()->create(['sponsor_id' => $root->id, 'placement_side' => 'right']);

        // Create binary trees
        foreach ([$root, $leftChild, $rightChild] as $user) {
            BinaryTree::create([
                'user_id' => $user->id,
                'total_left_volume' => 0,
                'total_right_volume' => 0,
                'left_consumed' => 0,
                'right_consumed' => 0,
                'level_index' => 1,
                'reward_count' => 0,
                'direct_pairs_paid' => 0,
                'spillover_pairs_paid' => 0,
                'left_spillover' => 0,
                'right_spillover' => 0,
            ]);
        }

        // Update binary tree structure
        $rootTree = BinaryTree::where('user_id', $root->id)->first();
        $rootTree->update([
            'left_child_id' => $leftChild->id,
            'right_child_id' => $rightChild->id,
        ]);

        // Test genealogy service
        $genealogy = $this->genealogyService->getGenealogyTree($root);

        $this->assertNotNull($genealogy);
        $this->assertEquals($root->id, $genealogy['user']->id);
        $this->assertArrayHasKey('left', $genealogy['children']);
        $this->assertArrayHasKey('right', $genealogy['children']);

        // Test network stats
        $stats = $this->genealogyService->getUserNetworkStats($root);
        $this->assertEquals(2, $stats['total_downlines']);
        $this->assertEquals(1, $stats['left_downlines']);
        $this->assertEquals(1, $stats['right_downlines']);
    }

    /** @test */
    public function test_bonus_status_management()
    {
        $user = User::factory()->create();
        $bonus = Bonus::create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'reward_type' => 'direct',
            'description' => 'Test bonus',
            'status' => 'pending',
        ]);

        // Test approval
        $bonus->approve();
        $this->assertEquals('approved', $bonus->fresh()->status);
        $this->assertNotNull($bonus->fresh()->approved_at);

        // Test marking as paid
        $bonus->markAsPaid();
        $this->assertEquals('paid', $bonus->fresh()->status);
        $this->assertNotNull($bonus->fresh()->paid_at);
    }

    /** @test */
    public function test_referral_code_expiration()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Generate code with expiration
        $codes = $this->codeService->generateBatch($admin, 1, 'Test', 1); // 1 day expiration

        $code = AdminCode::where('code', $codes[0])->first();
        $this->assertTrue($code->isAvailable());

        // Test after expiration
        $code->update(['expires_at' => now()->subDay()]);
        $this->assertFalse($code->isAvailable());
        $this->assertTrue($code->isExpired());
    }

    /** @test */
    public function test_volume_propagation_and_carryover()
    {
        // Create user hierarchy
        $root = User::factory()->create();
        $child = User::factory()->create(['sponsor_id' => $root->id]);

        // Create binary trees
        foreach ([$root, $child] as $user) {
            BinaryTree::create([
                'user_id' => $user->id,
                'total_left_volume' => 0,
                'total_right_volume' => 0,
                'left_consumed' => 0,
                'right_consumed' => 0,
                'level_index' => 1,
                'reward_count' => 0,
                'direct_pairs_paid' => 0,
                'spillover_pairs_paid' => 0,
                'left_spillover' => 0,
                'right_spillover' => 0,
            ]);
        }

        // Place user and propagate volume
        $this->binaryService->placeUser($child, $root, 'left');

        // Check volume propagation
        $rootTree = BinaryTree::where('user_id', $root->id)->first();
        $this->assertEquals(1, $rootTree->total_left_volume);
    }
}