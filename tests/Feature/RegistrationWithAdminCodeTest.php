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
use Illuminate\Support\Facades\Hash;

class RegistrationWithAdminCodeTest extends TestCase
{
    use RefreshDatabase;

    private User $sponsor;
    private AdminCode $adminCode;

    protected function setUp(): void
    {
        parent::setUp();

        // Create sponsor user
        $this->sponsor = User::factory()->create([
            'is_admin' => false,
            'email' => 'sponsor@test.com'
        ]);

        // Create admin code assigned to sponsor
        $this->adminCode = AdminCode::factory()->create([
            'code' => 'TESTCODE',
            'status' => 'issued',
            'distributor_id' => $this->sponsor->id
        ]);
    }

    public function test_successful_registration_with_valid_admin_code()
    {
        // Setup: ensure admin code is issued and assigned to sponsor
        $this->adminCode->update(['status' => 'issued']);

        // Action: register new user with valid admin code
        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => 'testcode' // lowercase should work due to case-insensitive
        ]);

        // Assertions
        $response->assertRedirect('/dashboard'); // Should redirect to dashboard

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'sponsor_id' => $this->sponsor->id,
            'registration_code' => 'TESTCODE'
        ]);

        $newUser = User::where('email', 'newuser@test.com')->first();
        $this->assertNotNull($newUser);

        // Verify admin code was consumed
        $this->assertDatabaseHas('admin_codes', [
            'id' => $this->adminCode->id,
            'status' => 'used',
            'used_by_user_id' => $newUser->id
        ]);

        // Verify binary tree was created
        $this->assertDatabaseHas('binary_trees', [
            'user_id' => $newUser->id
        ]);

        // Verify user was logged in
        $this->assertAuthenticatedAs($newUser);
    }

    public function test_registration_fails_with_invalid_admin_code()
    {
        // Action: try to register with invalid code
        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => 'INVALIDCODE'
        ]);

        // Assertions
        $response->assertRedirect();
        $response->assertSessionHasErrors(['registration_code' => 'Registration code not found']);

        // Verify user was not created
        $this->assertDatabaseMissing('users', [
            'email' => 'newuser@test.com'
        ]);

        // Verify admin code was not consumed
        $this->assertDatabaseHas('admin_codes', [
            'id' => $this->adminCode->id,
            'status' => 'issued'
        ]);
    }

    public function test_registration_fails_with_used_admin_code()
    {
        // Setup: mark admin code as used
        $usedByUser = User::factory()->create();
        $this->adminCode->update([
            'status' => 'used',
            'used_by_user_id' => $usedByUser->id,
            'used_at' => now()
        ]);

        // Action: try to register with already used code
        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => 'testcode'
        ]);

        // Assertions
        $response->assertRedirect();
        $response->assertSessionHasErrors(['registration_code' => 'Registration code is not valid or has already been used']);

        // Verify user was not created
        $this->assertDatabaseMissing('users', [
            'email' => 'newuser@test.com'
        ]);
    }

    public function test_registration_fails_with_code_not_assigned_to_distributor()
    {
        // Setup: create code without distributor
        $codeWithoutDistributor = AdminCode::factory()->create([
            'code' => 'ORPHANCODE',
            'status' => 'issued',
            'distributor_id' => null
        ]);

        // Action: try to register with code not assigned to distributor
        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => 'orphancode'
        ]);

        // Assertions
        $response->assertRedirect();
        $response->assertSessionHasErrors(['registration_code' => 'Invalid registration code - sponsor not found']);

        // Verify user was not created
        $this->assertDatabaseMissing('users', [
            'email' => 'newuser@test.com'
        ]);
    }

    public function test_registration_fails_with_missing_registration_code()
    {
        // Action: try to register without registration code
        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
            // Missing registration_code
        ]);

        // Assertions
        $response->assertRedirect();
        $response->assertSessionHasErrors(['registration_code' => 'The registration code field is required.']);

        // Verify user was not created
        $this->assertDatabaseMissing('users', [
            'email' => 'newuser@test.com'
        ]);
    }

    public function test_registration_creates_binary_tree_structure()
    {
        // Setup: ensure admin code is issued
        $this->adminCode->update(['status' => 'issued']);

        // Action: register new user
        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => 'testcode'
        ]);

        // Assertions
        $response->assertRedirect('/dashboard');

        $newUser = User::where('email', 'newuser@test.com')->first();
        $this->assertNotNull($newUser);

        // Verify binary tree structure
        $this->assertDatabaseHas('binary_trees', [
            'user_id' => $newUser->id
        ]);

        $this->assertDatabaseHas('binary_trees', [
            'user_id' => $this->sponsor->id
        ]);

        // Verify user placement
        $newUser->refresh();
        $this->assertEquals($this->sponsor->id, $newUser->sponsor_id);
        $this->assertNotNull($newUser->placement_side);
    }

    public function test_registration_triggers_bonus_creation()
    {
        // Setup: ensure admin code is issued
        $this->adminCode->update(['status' => 'issued']);

        // Action: register new user
        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => 'testcode'
        ]);

        // Assertions
        $response->assertRedirect('/dashboard');

        $newUser = User::where('email', 'newuser@test.com')->first();
        $this->assertNotNull($newUser);

        // Verify bonuses were created (direct bonus for sponsor)
        $this->assertDatabaseHas('bonuses', [
            'user_id' => $this->sponsor->id,
            'reward_type' => 'direct',
            'amount' => 100, // Fixed ₱100 reward
        ]);

        // Verify bonus was actually created
        $bonus = \App\Models\Bonus::where('user_id', $this->sponsor->id)
            ->where('reward_type', 'direct')
            ->first();
        $this->assertNotNull($bonus, 'Direct bonus should be created for sponsor');

        // Debug: Check if any bonuses exist at all
        $allBonuses = \App\Models\Bonus::all();
        $this->assertGreaterThan(0, $allBonuses->count(), 'At least one bonus should be created');

        // Verify volume propagation
        $sponsorTree = BinaryTree::where('user_id', $this->sponsor->id)->first();
        $this->assertGreaterThan(0, $sponsorTree->total_left_volume + $sponsorTree->total_right_volume, 'Volume should be propagated to sponsor');
    }

    public function test_registration_handles_concurrent_requests()
    {
        // Setup: ensure admin code is issued
        $this->adminCode->update(['status' => 'issued']);

        // Action: two users try to register with same code simultaneously
        $response1 = $this->post(route('register'), [
            'name' => 'User 1',
            'email' => 'user1@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => 'testcode'
        ]);

        $response2 = $this->post(route('register'), [
            'name' => 'User 2',
            'email' => 'user2@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => 'testcode'
        ]);

        // Assertions: only one should succeed
        $user1 = User::where('email', 'user1@test.com')->first();
        $user2 = User::where('email', 'user2@test.com')->first();

        // Only one user should be created
        $this->assertTrue(($user1 !== null) !== ($user2 !== null));

        // Admin code should be used by only one user
        $this->adminCode->refresh();
        $this->assertEquals('used', $this->adminCode->status);
        $this->assertNotNull($this->adminCode->used_by_user_id);
    }

    public function test_registration_validation_errors()
    {
        // Setup: ensure admin code is issued
        $this->adminCode->update(['status' => 'issued']);

        // Action: register with validation errors
        $response = $this->post(route('register'), [
            'name' => '', // Empty name
            'email' => 'invalid-email', // Invalid email
            'password' => '123', // Too short
            'password_confirmation' => '456', // Doesn't match
            'registration_code' => 'testcode'
        ]);

        // Assertions
        $response->assertRedirect();
        $response->assertSessionHasErrors(['name', 'email', 'password']);

        // Verify user was not created
        $this->assertDatabaseMissing('users', [
            'email' => 'invalid-email'
        ]);

        // Verify admin code was not consumed
        $this->assertDatabaseHas('admin_codes', [
            'id' => $this->adminCode->id,
            'status' => 'issued'
        ]);
    }

    public function test_registration_creates_proper_user_relationships()
    {
        // Setup: ensure admin code is issued
        $this->adminCode->update(['status' => 'issued']);

        // Action: register new user
        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => 'testcode'
        ]);

        $response->assertRedirect('/dashboard');

        $newUser = User::where('email', 'newuser@test.com')->first();
        $this->assertNotNull($newUser);

        // Verify user relationships
        $this->assertEquals($this->sponsor->id, $newUser->sponsor_id);
        $this->assertEquals('testcode', $newUser->registration_code);

        // Verify sponsor can access the new user as downline
        $downlines = $this->sponsor->downlines;
        $this->assertTrue($downlines->contains($newUser));

        // Verify new user can access sponsor
        $this->assertEquals($this->sponsor->id, $newUser->sponsor->id);
    }

    public function test_registration_handles_database_transaction_rollback()
    {
        // Setup: ensure admin code is issued
        $this->adminCode->update(['status' => 'issued']);

        // Mock the BinaryBalancerService to throw an exception
        $this->mock(BinaryBalancerService::class, function ($mock) {
            $mock->shouldReceive('placeUser')->andThrow(new \Exception('Test exception'));
        });

        // Action: register user (should fail due to mocked exception)
        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => 'testcode'
        ]);

        // Assertions
        $response->assertRedirect();
        $response->assertSessionHasErrors(['registration_code']);
        $response->assertSessionHasErrors('Registration failed. Please try again.');

        // Verify transaction was rolled back - user not created
        $this->assertDatabaseMissing('users', [
            'email' => 'newuser@test.com'
        ]);

        // Verify admin code was not consumed
        $this->assertDatabaseHas('admin_codes', [
            'id' => $this->adminCode->id,
            'status' => 'issued'
        ]);

        // Verify no binary tree was created
        $this->assertDatabaseMissing('binary_trees', [
            'user_id' => $this->adminCode->distributor_id // Would be the new user's ID
        ]);
    }
}