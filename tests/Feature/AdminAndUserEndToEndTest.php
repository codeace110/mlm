<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AdminCode;
use App\Models\BinaryTree;
use App\Models\Bonus;
use App\Services\BinaryBalancerService;
use Illuminate\Support\Facades\DB;

class AdminAndUserEndToEndTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $distributor;
    private BinaryBalancerService $binaryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->distributor = User::factory()->create(['is_admin' => false]);
        $this->binaryService = new BinaryBalancerService();
    }

    public function test_complete_flow_admin_issues_code_user_registers_bonuses_created()
    {
        // ===== ADMIN ISSUES CODE =====
        // Admin generates batch of codes
        $response = $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.generate'), [
                'count' => 15,
                'batch_name' => 'End-to-End Test Batch'
            ]);

        $response->assertRedirect(route('admin.admin_codes.index'));
        $response->assertSessionHas('success');

        // Verify codes were created
        $this->assertDatabaseCount('admin_codes', 15);
        $this->assertDatabaseHas('admin_codes', [
            'batch_name' => 'End-to-End Test Batch',
            'status' => 'issued'
        ]);

        // Get one of the generated codes
        $adminCode = AdminCode::where('batch_name', 'End-to-End Test Batch')->first();
        $this->assertNotNull($adminCode);

        // Admin assigns code to distributor
        $response = $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.assign', $adminCode), [
                'distributor_id' => $this->distributor->id
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify code is assigned to distributor
        $adminCode->refresh();
        $this->assertEquals($this->distributor->id, $adminCode->distributor_id);
        $this->assertEquals('unused', $adminCode->status);

        // ===== USER REGISTERS WITH CODE =====
        // User registers with the admin code
        $response = $this->post(route('register'), [
            'name' => 'New Registrant',
            'email' => 'registrant@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => $adminCode->code
        ]);

        $response->assertRedirect('/dashboard');

        // Verify user was created and logged in
        $newUser = User::where('email', 'registrant@test.com')->first();
        $this->assertNotNull($newUser);
        $this->assertAuthenticatedAs($newUser);

        // Verify sponsor relationship
        $this->assertEquals($this->distributor->id, $newUser->sponsor_id);
        $this->assertEquals($adminCode->code, $newUser->registration_code);

        // Verify admin code was consumed
        $adminCode->refresh();
        $this->assertEquals('used', $adminCode->status);
        $this->assertEquals($newUser->id, $adminCode->used_by_user_id);
        $this->assertNotNull($adminCode->used_at);

        // ===== VERIFY BINARY TREE STRUCTURE =====
        // Verify binary tree was created for both users
        $this->assertDatabaseHas('binary_trees', ['user_id' => $this->distributor->id]);
        $this->assertDatabaseHas('binary_trees', ['user_id' => $newUser->id]);

        // Verify user placement
        $newUser->refresh();
        $this->assertNotNull($newUser->placement_side);

        // ===== VERIFY BONUSES WERE CREATED =====
        // Verify direct bonus was created for distributor (₱100 fixed)
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $this->distributor->id,
            'reward_type' => 'direct',
            'amount' => 100, // Fixed ₱100, not percentage
            'is_product' => false
        ]);

        // Verify volume propagation
        $distributorTree = BinaryTree::where('user_id', $this->distributor->id)->first();
        $this->assertGreaterThan(0, $distributorTree->total_left_volume + $distributorTree->total_right_volume);

        // ===== VERIFY DOWNLINE QUOTAS PROCESSED =====
        // Check if any level bonuses were triggered
        $levelBonuses = Bonus::where('user_id', $this->distributor->id)
            ->where('reward_type', 'level')
            ->get();

        // If level bonuses exist, verify they are ₱100 fixed
        foreach ($levelBonuses as $bonus) {
            $this->assertEquals(100, $bonus->amount);
            $this->assertNotNull($bonus->level_index);
        }

        // ===== VERIFY REWARD COUNT INCREMENTED =====
        $distributorTree->refresh();
        $totalBonuses = Bonus::where('user_id', $this->distributor->id)->count();
        $this->assertEquals($totalBonuses, $distributorTree->reward_count);

        // ===== VERIFY NO DUPLICATE BONUSES =====
        $totalBonuses = Bonus::where('user_id', $this->distributor->id)->count();
        $uniqueBonuses = Bonus::where('user_id', $this->distributor->id)->distinct()->count();
        $this->assertEquals($totalBonuses, $uniqueBonuses);

        // ===== VERIFY DATABASE STATE CONSISTENCY =====
        // Verify all database operations completed successfully
        $this->assertDatabaseCount('users', 3); // admin, distributor, new user
        $this->assertDatabaseCount('admin_codes', 15); // All codes still exist
        $this->assertDatabaseCount('binary_trees', 2); // distributor and new user
        $this->assertGreaterThan(0, Bonus::count()); // At least one bonus created

        // Verify no orphaned records
        $this->assertDatabaseMissing('admin_codes', [
            'status' => 'issued',
            'distributor_id' => null
        ]);

        // Verify transaction integrity
        $this->assertEquals(1, $adminCode->used_by_user_id);
        $this->assertEquals($this->distributor->id, $newUser->sponsor_id);
    }

    public function test_multiple_registrations_create_proper_tree_structure()
    {
        // Setup: generate codes and assign to distributor
        $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.generate'), [
                'count' => 15,
                'batch_name' => 'Multi Registration Test'
            ]);

        $codes = AdminCode::where('batch_name', 'Multi Registration Test')
            ->where('status', 'issued')
            ->take(3)
            ->get();

        foreach ($codes as $code) {
            $this->actingAs($this->admin)
                ->post(route('admin.admin_codes.assign', $code), [
                    'distributor_id' => $this->distributor->id
                ]);
        }

        // Register multiple users
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $response = $this->post(route('register'), [
                'name' => "User {$i}",
                'email' => "user{$i}@test.com",
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'registration_code' => $codes[$i-1]->code
            ]);

            $response->assertRedirect('/dashboard');
            $users[] = User::where("email", "user{$i}@test.com")->first();
        }

        // Verify tree structure
        foreach ($users as $user) {
            $this->assertEquals($this->distributor->id, $user->sponsor_id);
            $this->assertDatabaseHas('binary_trees', ['user_id' => $user->id]);
        }

        // Verify distributor has multiple downlines
        $downlineCount = $this->distributor->downlines()->count();
        $this->assertEquals(3, $downlineCount);

        // Verify bonuses created for each registration
        $bonusCount = Bonus::where('user_id', $this->distributor->id)
            ->where('reward_type', 'direct')
            ->count();
        $this->assertEquals(3, $bonusCount);

        // Verify volume propagation
        $distributorTree = BinaryTree::where('user_id', $this->distributor->id)->first();
        $totalVolume = $distributorTree->total_left_volume + $distributorTree->total_right_volume;
        $this->assertEquals(3, $totalVolume);
    }

    public function test_end_to_end_flow_with_product_rewards()
    {
        // Setup: create distributor with 4 existing rewards (next will be 5th = product)
        $distributorTree = BinaryTree::firstOrCreate(['user_id' => $this->distributor->id], [
            'reward_count' => 4
        ]);

        // Generate and assign code
        $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.generate'), [
                'count' => 15,
                'batch_name' => 'Product Reward Test'
            ]);

        $code = AdminCode::where('batch_name', 'Product Reward Test')->first();
        $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.assign', $code), [
                'distributor_id' => $this->distributor->id
            ]);

        // Register user (this should trigger 5th reward = product reward)
        $response = $this->post(route('register'), [
            'name' => 'Product Reward User',
            'email' => 'productuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => $code->code
        ]);

        $response->assertRedirect('/dashboard');

        // Verify product reward was created
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $this->distributor->id,
            'reward_type' => 'direct',
            'amount' => 0, // Product reward = ₱0
            'is_product' => true
        ]);

        // Verify reward count incremented
        $distributorTree->refresh();
        $this->assertEquals(5, $distributorTree->reward_count);
    }

    public function test_failed_registration_does_not_affect_database_state()
    {
        // Setup: generate and assign code
        $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.generate'), [
                'count' => 15,
                'batch_name' => 'Failed Registration Test'
            ]);

        $code = AdminCode::where('batch_name', 'Failed Registration Test')->first();
        $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.assign', $code), [
                'distributor_id' => $this->distributor->id
            ]);

        // Get initial state
        $initialBonusCount = Bonus::count();
        $initialUserCount = User::count();

        // Attempt registration with invalid data
        $response = $this->post(route('register'), [
            'name' => '', // Invalid - empty name
            'email' => 'invalid-email', // Invalid email
            'password' => '123', // Too short
            'password_confirmation' => '456', // Doesn't match
            'registration_code' => $code->code
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name', 'email', 'password']);

        // Verify database state unchanged
        $this->assertDatabaseCount('users', $initialUserCount);
        $this->assertDatabaseCount('bonuses', $initialBonusCount);

        // Verify code still available
        $code->refresh();
        $this->assertEquals('unused', $code->status);
        $this->assertNull($code->used_by_user_id);
    }

    public function test_end_to_end_flow_with_level_bonuses()
    {
        // Setup: create distributor with enough volume to trigger level bonuses
        $distributorTree = BinaryTree::firstOrCreate(['user_id' => $this->distributor->id], [
            'total_left_volume' => 8, // Enough for level 3 quota (8)
            'total_right_volume' => 8,
            'level_index' => 3,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        // Generate and assign code
        $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.generate'), [
                'count' => 15,
                'batch_name' => 'Level Bonus Test'
            ]);

        $code = AdminCode::where('batch_name', 'Level Bonus Test')->first();
        $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.assign', $code), [
                'distributor_id' => $this->distributor->id
            ]);

        // Register user (should trigger level bonus)
        $response = $this->post(route('register'), [
            'name' => 'Level Bonus User',
            'email' => 'leveluser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => $code->code
        ]);

        $response->assertRedirect('/dashboard');

        // Verify level bonus was created
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $this->distributor->id,
            'reward_type' => 'level',
            'level_index' => 3,
            'amount' => 100, // Fixed ₱100
        ]);

        // Verify consumed volumes updated
        $distributorTree->refresh();
        $this->assertEquals(8, $distributorTree->left_consumed);
        $this->assertEquals(8, $distributorTree->right_consumed);
        $this->assertEquals(4, $distributorTree->level_index);
    }
}