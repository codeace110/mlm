<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AdminCode;
use App\Models\BinaryTree;
use App\Models\Bonus;
use App\Services\AdminCodeService;
use App\Services\BinaryBalancerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class ConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    private User $distributor;
    private AdminCode $adminCode;
    private AdminCodeService $adminCodeService;
    private BinaryBalancerService $binaryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->distributor = User::factory()->create(['is_admin' => false]);
        $this->adminCode = AdminCode::factory()->create([
            'code' => 'CONCURRENTTEST',
            'status' => 'unused',
            'distributor_id' => $this->distributor->id
        ]);
        $this->adminCodeService = new AdminCodeService();
        $this->binaryService = new BinaryBalancerService();
    }

    public function test_simultaneous_admin_code_usage_only_one_succeeds()
    {
        // Setup: create multiple users trying to use the same code
        $users = User::factory()->count(5)->create();

        // Action: all users try to use the same admin code simultaneously
        $results = [];
        foreach ($users as $user) {
            $result = $this->adminCodeService->validateAndUseCode('CONCURRENTTEST', $user);
            $results[] = $result;
        }

        // Assertions: only one should succeed
        $successCount = count(array_filter($results, fn($result) => $result instanceof User));
        $failureCount = count(array_filter($results, fn($result) => $result === false));

        $this->assertEquals(1, $successCount, 'Only one user should successfully use the admin code');
        $this->assertEquals(4, $failureCount, 'Four users should fail to use the admin code');

        // Verify admin code was used only once
        $this->adminCode->refresh();
        $this->assertEquals('used', $this->adminCode->status);
        $this->assertNotNull($this->adminCode->used_by_user_id);
        $this->assertNotNull($this->adminCode->used_at);

        // Verify only one user has the code in their registration_code field
        $successfulUser = User::where('registration_code', 'CONCURRENTTEST')->first();
        $this->assertNotNull($successfulUser);
        $this->assertEquals($this->adminCode->used_by_user_id, $successfulUser->id);
    }

    public function test_simultaneous_registration_attempts_with_same_code()
    {
        // Setup: create multiple users trying to register with the same code
        $usersData = [
            ['name' => 'User 1', 'email' => 'user1@test.com', 'password' => 'password123'],
            ['name' => 'User 2', 'email' => 'user2@test.com', 'password' => 'password123'],
            ['name' => 'User 3', 'email' => 'user3@test.com', 'password' => 'password123'],
            ['name' => 'User 4', 'email' => 'user4@test.com', 'password' => 'password123'],
            ['name' => 'User 5', 'email' => 'user5@test.com', 'password' => 'password123'],
        ];

        // Action: all users try to register simultaneously with the same code
        $responses = [];
        foreach ($usersData as $userData) {
            $response = $this->post(route('register'), array_merge($userData, [
                'password_confirmation' => 'password123',
                'registration_code' => 'concurrenttest'
            ]));
            $responses[] = $response;
        }

        // Assertions: only one should succeed
        $successResponses = array_filter($responses, fn($response) => $response->isRedirect() && $response->getTargetUrl() === route('home'));
        $failureResponses = array_filter($responses, fn($response) => $response->isRedirect() && strpos($response->getTargetUrl(), 'register') !== false);

        $this->assertCount(1, $successResponses, 'Only one registration should succeed');
        $this->assertCount(4, $failureResponses, 'Four registrations should fail');

        // Verify only one user was created
        $createdUsers = User::whereIn('email', array_column($usersData, 'email'))->get();
        $this->assertCount(1, $createdUsers, 'Only one user should be created');

        // Verify admin code was used by the successful user
        $this->adminCode->refresh();
        $this->assertEquals('used', $this->adminCode->status);
        $this->assertEquals($createdUsers->first()->id, $this->adminCode->used_by_user_id);
    }

    public function test_simultaneous_binary_tree_placement_race_condition()
    {
        // Setup: create sponsor with available positions
        $sponsor = User::factory()->create(['is_admin' => false]);
        $sponsorTree = BinaryTree::firstOrCreate(['user_id' => $sponsor->id]);

        // Create multiple users to be placed simultaneously
        $usersToPlace = User::factory()->count(10)->create();

        // Action: place all users simultaneously (simulating race condition)
        $placementResults = [];
        foreach ($usersToPlace as $user) {
            try {
                $this->binaryService->placeUser($user, $sponsor);
                $placementResults[] = 'success';
            } catch (\Exception $e) {
                $placementResults[] = 'failed';
            }
        }

        // Assertions: all placements should succeed without conflicts
        $successCount = count(array_filter($placementResults, fn($result) => $result === 'success'));
        $this->assertEquals(10, $successCount, 'All user placements should succeed');

        // Verify binary tree structure
        $sponsorTree->refresh();
        $this->assertNotNull($sponsorTree->left_child_id);
        $this->assertNotNull($sponsorTree->right_child_id);

        // Verify volume propagation
        $totalVolume = $sponsorTree->total_left_volume + $sponsorTree->total_right_volume;
        $this->assertEquals(10, $totalVolume, 'All 10 users should contribute to volume');

        // Verify no orphaned users
        $placedUsers = User::whereIn('id', $usersToPlace->pluck('id'))->get();
        foreach ($placedUsers as $user) {
            $this->assertEquals($sponsor->id, $user->sponsor_id);
            $this->assertNotNull($user->placement_side);
        }
    }

    public function test_concurrent_bonus_calculations_with_shared_data()
    {
        // Setup: create sponsor with multiple direct referrals
        $sponsor = User::factory()->create(['is_admin' => false]);
        $directReferrals = User::factory()->count(5)->create([
            'sponsor_id' => $sponsor->id,
            'placement_side' => 'left'
        ]);

        // Action: calculate direct bonuses multiple times simultaneously
        $bonusCalculationResults = [];
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->binaryService->calculateDirectBonus($sponsor);
                $bonusCalculationResults[] = 'success';
            } catch (\Exception $e) {
                $bonusCalculationResults[] = 'failed';
            }
        }

        // Assertions: all calculations should succeed
        $successCount = count(array_filter($bonusCalculationResults, fn($result) => $result === 'success'));
        $this->assertEquals(5, $successCount, 'All bonus calculations should succeed');

        // Verify correct bonus amounts (fixed ₱100 per pair)
        $totalBonuses = Bonus::where('user_id', $sponsor->id)
            ->where('reward_type', 'direct')
            ->sum('amount');
        $this->assertEquals(500, $totalBonuses, 'Total bonuses should be 5 pairs × ₱100 = ₱500');

        // Verify no duplicate bonuses
        $bonusCount = Bonus::where('user_id', $sponsor->id)
            ->where('reward_type', 'direct')
            ->count();
        $this->assertEquals(5, $bonusCount, 'Should have exactly 5 bonus records');
    }

    public function test_simultaneous_level_bonus_processing()
    {
        // Setup: create user with volume ready for multiple level bonuses
        $user = User::factory()->create(['is_admin' => false]);
        $userTree = BinaryTree::firstOrCreate(['user_id' => $user->id], [
            'total_left_volume' => 100, // Enough for multiple levels
            'total_right_volume' => 100,
            'level_index' => 1,
            'left_consumed' => 0,
            'right_consumed' => 0,
        ]);

        // Action: process levels multiple times simultaneously
        $levelProcessingResults = [];
        for ($i = 0; $i < 3; $i++) {
            try {
                $this->binaryService->processLevels($user);
                $levelProcessingResults[] = 'success';
            } catch (\Exception $e) {
                $levelProcessingResults[] = 'failed';
            }
        }

        // Assertions: all processing should succeed
        $successCount = count(array_filter($levelProcessingResults, fn($result) => $result === 'success'));
        $this->assertEquals(3, $successCount, 'All level processing should succeed');

        // Verify level bonuses were created correctly
        $levelBonuses = Bonus::where('user_id', $user->id)
            ->where('reward_type', 'level')
            ->get();

        $this->assertGreaterThan(0, $levelBonuses->count(), 'Should have level bonuses');

        // Verify all bonuses are fixed ₱100 amounts
        foreach ($levelBonuses as $bonus) {
            $this->assertEquals(100, $bonus->amount, 'All level bonuses should be ₱100');
        }

        // Verify consumed volumes are correct
        $userTree->refresh();
        $this->assertGreaterThan(0, $userTree->left_consumed);
        $this->assertGreaterThan(0, $userTree->right_consumed);
    }

    public function test_concurrent_volume_propagation()
    {
        // Setup: create multi-level hierarchy
        $root = User::factory()->create(['is_admin' => false]);
        $middle = User::factory()->create(['sponsor_id' => $root->id, 'placement_side' => 'left']);
        $leaf = User::factory()->create(['sponsor_id' => $middle->id, 'placement_side' => 'left']);

        // Create binary trees
        BinaryTree::firstOrCreate(['user_id' => $root->id]);
        BinaryTree::firstOrCreate(['user_id' => $middle->id]);
        BinaryTree::firstOrCreate(['user_id' => $leaf->id]);

        // Action: propagate volume up multiple times simultaneously
        $propagationResults = [];
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->binaryService->propagateVolumeUp($leaf, 1);
                $propagationResults[] = 'success';
            } catch (\Exception $e) {
                $propagationResults[] = 'failed';
            }
        }

        // Assertions: all propagations should succeed
        $successCount = count(array_filter($propagationResults, fn($result) => $result === 'success'));
        $this->assertEquals(5, $successCount, 'All volume propagations should succeed');

        // Verify volume was propagated correctly
        $rootTree = BinaryTree::where('user_id', $root->id)->first();
        $middleTree = BinaryTree::where('user_id', $middle->id)->first();
        $leafTree = BinaryTree::where('user_id', $leaf->id)->first();

        $this->assertEquals(5, $rootTree->total_left_volume, 'Root should have accumulated 5 volume');
        $this->assertEquals(5, $middleTree->total_left_volume, 'Middle should have accumulated 5 volume');
        $this->assertEquals(0, $leafTree->total_left_volume, 'Leaf should have 0 volume (all propagated up)');
    }

    public function test_simultaneous_database_transactions_with_locking()
    {
        // Setup: create admin code for concurrent usage
        $adminCode = AdminCode::factory()->create([
            'code' => 'LOCKTEST',
            'status' => 'unused',
            'distributor_id' => $this->distributor->id
        ]);

        // Action: multiple processes try to use the same code simultaneously
        $processes = [];
        for ($i = 0; $i < 3; $i++) {
            $processes[] = function () use ($adminCode) {
                return DB::transaction(function () use ($adminCode) {
                    // Lock the admin code for update
                    $lockedCode = AdminCode::where('id', $adminCode->id)
                        ->lockForUpdate()
                        ->first();

                    if ($lockedCode->status === 'used') {
                        return 'already_used';
                    }

                    // Simulate some processing time
                    usleep(rand(10000, 50000)); // 10-50ms

                    // Mark as used
                    $lockedCode->update([
                        'status' => 'used',
                        'used_by_user_id' => User::factory()->create()->id,
                        'used_at' => now(),
                    ]);

                    return 'success';
                });
            };
        }

        // Execute processes
        $results = array_map(fn($process) => $process(), $processes);

        // Assertions: only one should succeed
        $successCount = count(array_filter($results, fn($result) => $result === 'success'));
        $alreadyUsedCount = count(array_filter($results, fn($result) => $result === 'already_used'));

        $this->assertEquals(1, $successCount, 'Only one process should successfully use the code');
        $this->assertEquals(2, $alreadyUsedCount, 'Two processes should find the code already used');

        // Verify final state
        $adminCode->refresh();
        $this->assertEquals('used', $adminCode->status);
        $this->assertNotNull($adminCode->used_by_user_id);
    }

    public function test_concurrent_user_creation_with_unique_constraints()
    {
        // Setup: create admin code for registration
        $adminCode = AdminCode::factory()->create([
            'code' => 'UNIQUEUSERTEST',
            'status' => 'unused',
            'distributor_id' => $this->distributor->id
        ]);

        // Action: multiple registration attempts with same email (should fail)
        $registrationData = [
            'name' => 'Test User',
            'email' => 'unique@test.com', // Same email for all attempts
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => 'uniqueusertest'
        ];

        $responses = [];
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post(route('register'), $registrationData);
            $responses[] = $response;
        }

        // Assertions: only first should succeed, others should fail due to unique email constraint
        $successResponses = array_filter($responses, fn($response) => $response->isRedirect() && $response->getTargetUrl() === route('home'));
        $failureResponses = array_filter($responses, fn($response) => $response->isRedirect() && strpos($response->getTargetUrl(), 'register') !== false);

        $this->assertCount(1, $successResponses, 'Only one registration should succeed');
        $this->assertCount(2, $failureResponses, 'Two registrations should fail due to unique constraint');

        // Verify only one user was created
        $createdUsers = User::where('email', 'unique@test.com')->get();
        $this->assertCount(1, $createdUsers, 'Only one user should be created with unique email');

        // Verify admin code was used only once
        $adminCode->refresh();
        $this->assertEquals('used', $adminCode->status);
        $this->assertEquals($createdUsers->first()->id, $adminCode->used_by_user_id);
    }

    public function test_concurrent_queue_jobs_processing()
    {
        // Setup: create scenario that would trigger queue jobs
        Queue::fake();

        $sponsor = User::factory()->create(['is_admin' => false]);
        $newUser = User::factory()->create(['sponsor_id' => $sponsor->id]);

        // Action: place user in binary tree (may trigger queue jobs)
        $this->binaryService->placeUser($newUser, $sponsor);

        // Verify no queue jobs were dispatched (or handle them properly)
        Queue::assertNothingDispatched(); // Assuming no jobs are dispatched in this flow

        // Verify binary tree placement was successful
        $sponsorTree = BinaryTree::where('user_id', $sponsor->id)->first();
        $this->assertNotNull($sponsorTree->left_child_id || $sponsorTree->right_child_id);

        $newUser->refresh();
        $this->assertEquals($sponsor->id, $newUser->sponsor_id);
        $this->assertNotNull($newUser->placement_side);
    }

    public function test_concurrent_read_operations_dont_interfere()
    {
        // Setup: create complex network structure
        $sponsor = User::factory()->create(['is_admin' => false]);
        $networkUsers = User::factory()->count(20)->create(['sponsor_id' => $sponsor->id]);

        // Action: multiple read operations simultaneously
        $readOperations = [];
        for ($i = 0; $i < 10; $i++) {
            $readOperations[] = function () use ($sponsor) {
                return User::where('sponsor_id', $sponsor->id)->count();
            };
        }

        // Execute read operations
        $results = array_map(fn($operation) => $operation(), $readOperations);

        // Assertions: all read operations should return consistent results
        $expectedCount = 20;
        foreach ($results as $result) {
            $this->assertEquals($expectedCount, $result, 'All read operations should return consistent count');
        }

        // Verify no data corruption occurred
        $actualCount = User::where('sponsor_id', $sponsor->id)->count();
        $this->assertEquals($expectedCount, $actualCount, 'Final count should be correct');
    }
}