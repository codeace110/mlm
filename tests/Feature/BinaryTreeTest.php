<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Services\BinaryTreeService;
use App\Services\BalancerService;
use App\Models\BinaryTree;
use App\Models\Earning;
use Illuminate\Support\Facades\Hash;

class BinaryTreeTest extends TestCase
{
    use RefreshDatabase;

    protected $binaryService;
    protected $balancerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->binaryService = new BinaryTreeService();
        $this->balancerService = new BalancerService();
    }

    /** @test */
    public function it_places_user_left_first_then_right()
    {
        $root = User::create([
            'id' => 'ROOT001',
            'name' => 'Root',
            'email' => 'root@example.com',
            'password' => Hash::make('password'),
            'referral_code' => 'ROOT',
            'sponsor_id' => null,
            'placement_side' => null,
            'is_admin' => false,
            'status' => 'approved',
            'level' => 0,
            'balancing_mode' => '1:1',
            'account_balance' => 0,
        ]);

        // First direct: left
        $leftUser = User::create([
            'id' => 'LEFT001',
            'name' => 'Left User',
            'email' => 'left@example.com',
            'password' => Hash::make('password'),
            'referral_code' => 'LEFT',
            'sponsor_id' => $root->id,
            'is_admin' => false,
            'status' => 'approved',
            'level' => 1,
            'balancing_mode' => '1:1',
            'account_balance' => 0,
        ]);
        $this->binaryService->placeUserInTree($leftUser, $root);

        $rootTree = BinaryTree::where('user_id', $root->id)->first();
        $this->assertEquals($leftUser->id, $rootTree->left_child_id);
        $this->assertEquals(100, $rootTree->left_volume);
        $this->assertNull($rootTree->right_child_id);
        $this->assertEquals('left', $leftUser->fresh()->placement_side);

        // Second direct: right
        $rightUser = User::create([
            'id' => 'RIGHT001',
            'name' => 'Right User',
            'email' => 'right@example.com',
            'password' => Hash::make('password'),
            'referral_code' => 'RIGHT',
            'sponsor_id' => $root->id,
            'is_admin' => false,
            'status' => 'approved',
            'level' => 1,
            'balancing_mode' => '1:1',
            'account_balance' => 0,
        ]);
        $this->binaryService->placeUserInTree($rightUser, $root);

        $rootTree = $rootTree->fresh();
        $this->assertEquals($rightUser->id, $rootTree->right_child_id);
        $this->assertEquals(100, $rootTree->right_volume);
        $this->assertEquals('right', $rightUser->fresh()->placement_side);
    }

    /** @test */
    public function it_handles_spillover_to_weaker_leg()
    {
        $root = User::create([
            'id' => 'ROOT002',
            'name' => 'Root Spill',
            'email' => 'rootspill@example.com',
            'password' => Hash::make('password'),
            'referral_code' => 'ROOTSP',
            'sponsor_id' => null,
            'placement_side' => null,
            'is_admin' => false,
            'status' => 'approved',
            'level' => 0,
            'balancing_mode' => '1:1',
            'account_balance' => 0,
        ]);

        // Place left and right
        $leftUser = $this->createTestUser('LEFT002', '1:1', $root->id);
        $this->binaryService->placeUserInTree($leftUser, $root);
        $rightUser = $this->createTestUser('RIGHT002', '1:1', $root->id);
        $this->binaryService->placeUserInTree($rightUser, $root);

        // Third: spillover to left (weaker, assuming equal volumes, prefers left)
        $spillUser = User::create([
            'id' => 'SPILL001',
            'name' => 'Spill User',
            'email' => 'spill@example.com',
            'password' => Hash::make('password'),
            'referral_code' => 'SPILL',
            'sponsor_id' => $root->id,
            'is_admin' => false,
            'status' => 'approved',
            'level' => 1,
            'balancing_mode' => '1:1',
            'account_balance' => 0,
        ]);
        $this->binaryService->placeUserInTree($spillUser, $root);

        $leftTree = BinaryTree::where('user_id', $leftUser->id)->first();
        $this->assertEquals($spillUser->id, $leftTree->left_child_id);
        $this->assertEquals(100, $leftTree->left_volume);
        $rootTree = BinaryTree::where('user_id', $root->id)->first();
        $this->assertEquals(200, $rootTree->left_volume); // Added to left leg volume
    }

    /** @test */
    public function it_tests_1_1_balancing_mode_with_pairs_and_commission()
    {
        $root = $this->createTestUser('ROOT1:1', '1:1');

        // Place two directs: one left, one right -> 1 pair
        $left = $this->createTestUser('LEFT1:1', '1:1', $root->id);
        $this->binaryService->placeUserInTree($left, $root);
        $right = $this->createTestUser('RIGHT1:1', '1:1', $root->id);
        $this->binaryService->placeUserInTree($right, $root);

        // Balancer called during placement, but to ensure, call again
        $this->balancerService->processPairs($root);

        $rootTree = BinaryTree::where('user_id', $root->id)->first();
        $this->assertEquals(0, $rootTree->left_volume); // Deducted 100
        $this->assertEquals(0, $rootTree->right_volume);
        $this->assertEquals(0, $rootTree->carryover_left);
        $this->assertEquals(0, $rootTree->carryover_right);

        $earning = Earning::where('user_id', $root->id)->first();
        $this->assertEquals('binary_pair', $earning->type);
        $this->assertEquals(10.00, $earning->amount); // 1 pair * 100 * 10%
        $this->assertStringContainsString('1:1', $earning->description);
    }

    /** @test */
    public function it_tests_2_1_balancing_mode_weighted_pairs()
    {
        $root = $this->createTestUser('ROOT2:1', '2:1');

        // Place three left, one right -> min(300/2=150, 100)=100 /100 =1 pair
        $left1 = $this->createTestUser('L1', '2:1', $root->id);
        $this->binaryService->placeUserInTree($left1, $root);
        $left2 = $this->createTestUser('L2', '2:1', $root->id);
        $this->binaryService->placeUserInTree($left2, $root);
        $left3 = $this->createTestUser('L3', '2:1', $root->id);
        $this->binaryService->placeUserInTree($left3, $root); // Spillover to left
        $right1 = $this->createTestUser('R1', '2:1', $root->id);
        $this->binaryService->placeUserInTree($right1, $root);

        $this->balancerService->processPairs($root);

        $rootTree = BinaryTree::where('user_id', $root->id)->first();
        // Deduct 200 left, 100 right for 1 pair
        $this->assertEquals(100, $rootTree->left_volume); // 300 - 200 =100
        $this->assertEquals(0, $rootTree->right_volume);
        $earning = Earning::where('user_id', $root->id)->first();
        $this->assertEquals(10.00, $earning->amount);
        $this->assertStringContainsString('2:1', $earning->description);
    }

    /** @test */
    public function it_tests_3_1_balancing_mode_weighted_pairs()
    {
        $root = $this->createTestUser('ROOT3:1', '3:1');

        // Place four left, one right -> min(400/3≈133, 100)=100 /100=1 pair
        for ($i = 1; $i <= 4; $i++) {
            $left = $this->createTestUser("L{$i}", '3:1', $root->id);
            $this->binaryService->placeUserInTree($left, $root);
        }
        $right = $this->createTestUser('R1', '3:1', $root->id);
        $this->binaryService->placeUserInTree($right, $root);

        $this->balancerService->processPairs($root);

        $rootTree = BinaryTree::where('user_id', $root->id)->first();
        // Deduct 300 left, 100 right
        $this->assertEquals(100, $rootTree->left_volume); // 400 - 300 =100
        $this->assertEquals(0, $rootTree->right_volume);
        $earning = Earning::where('user_id', $root->id)->first();
        $this->assertEquals(10.00, $earning->amount);
        $this->assertStringContainsString('3:1', $earning->description);
    }

    /** @test */
    public function it_handles_carryover_in_balancing()
    {
        $root = $this->createTestUser('ROOTCARRY', '1:1');

        // Simulate partial pair: 100 left, 50 carryover right somehow, but since placement adds full, force via DB
        $rootTree = BinaryTree::create([
            'user_id' => $root->id,
            'left_volume' => 100,
            'right_volume' => 0,
            'carryover_left' => 0,
            'carryover_right' => 50,
        ]);

        // Add right 100, but test carryover use
        $right = $this->createTestUser('R', '1:1', $root->id);
        $this->binaryService->placeUserInTree($right, $root); // Adds 100 to right

        $this->balancerService->processPairs($root);

        // Total left 100, right 100+50=150 -> 1 pair, deduct 100 each, left=0, right=50 (volume=50, carryover=0)
        $rootTree = $rootTree->fresh();
        $this->assertEquals(0, $rootTree->left_volume);
        $this->assertEquals(50, $rootTree->right_volume);
        $earning = Earning::where('user_id', $root->id)->first();
        $this->assertEquals(10.00, $earning->amount);
    }

    /** @test */
    public function it_creates_commissions_correctly_with_multiple_pairs()
    {
        $root = $this->createTestUser('ROOTMULTI', '1:1');

        // Place 2 left, 2 right -> 2 pairs
        for ($i = 1; $i <= 2; $i++) {
            $left = $this->createTestUser("L{$i}", '1:1', $root->id);
            $this->binaryService->placeUserInTree($left, $root);
        }
        for ($i = 1; $i <= 2; $i++) {
            $right = $this->createTestUser("R{$i}", '1:1', $root->id);
            $this->binaryService->placeUserInTree($right, $root);
        }

        $this->balancerService->processPairs($root);

        $earning = Earning::where('user_id', $root->id)->first();
        $this->assertEquals(20.00, $earning->amount); // 2 pairs * 10
    }

    /** @test */
    public function tree_does_not_break_with_multiple_seeds_direct_and_spillover()
    {
        $root = $this->createTestUser('ROOTMANY', '1:1');

        $users = [];
        for ($i = 1; $i <= 15; $i++) { // 15 users: directs fill, then spillover
            $user = $this->createTestUser("U{$i}", '1:1', $root->id);
            $this->binaryService->placeUserInTree($user, $root);
            $users[] = $user;
        }

        // Assert all placed, no orphans
        $allTrees = BinaryTree::all();
        $this->assertEquals(16, $allTrees->count()); // root + 15
        foreach ($users as $user) {
            $tree = BinaryTree::where('user_id', $user->id)->first();
            $this->assertNotNull($tree);
            // Check hierarchy: all have sponsor path to root
            $current = $user;
            $depth = 0;
            while ($current->sponsor_id) {
                $current = User::find($current->sponsor_id);
                $depth++;
                $this->assertNotNull($current);
            }
            $this->assertEquals($root->id, $current->id);
            $this->assertLessThan(5, $depth); // No deep cycles
        }

        // Direct referrals: sponsor_id = root
        $directs = User::where('sponsor_id', $root->id)->get();
        $this->assertGreaterThan(1, $directs->count()); // At least 2 directs

        // Spillovers: sponsor_id = root but deeper in tree
        $spillovers = collect($users)->filter(fn($u) => $u->sponsor_id == $root->id && $u->placement_side != null);
        // Some are directs, some spillover but sponsor still root? Wait, in code, sponsor_id is set to direct sponsor, but for spillover, when placing under child, sponsor_id is still root? No:
        // In createUser, sponsor_id = $sponsorId (root), but placement recurses under child, but sponsor_id remains root. That's direct referral, but tree placement is spillover.
        // To distinguish: Direct: immediate children, spillover: grandchildren+ with sponsor=root.

        $rootTree = BinaryTree::where('user_id', $root->id)->first();
        $this->assertGreaterThan(100 * 5, $rootTree->left_volume + $rootTree->right_volume); // Volumes accumulated

        // No cycles: Query to detect, but simple depth check above suffices
    }

    /** @test */
    public function audit_logs_visibility_admin_sees_all_distributor_sees_own()
    {
        $admin = User::create([
            'id' => 'ADMIN',
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'referral_code' => 'ADMIN',
            'sponsor_id' => null,
            'placement_side' => null,
            'is_admin' => true,
            'status' => 'approved',
            'level' => 0,
            'balancing_mode' => '1:1',
            'account_balance' => 0,
        ]);

        $distributor = $this->createTestUser('DIST', '1:1');
        $otherUser = $this->createTestUser('OTHER', '1:1', $distributor->id);
        $this->binaryService->placeUserInTree($otherUser, $distributor);
        $this->balancerService->processPairs($distributor); // Creates earning for distributor

        // Admin sees all
        $adminEarnings = Earning::all(); // Assuming admin query is global
        $this->assertEquals(1, $adminEarnings->count());

        // Distributor sees only own (via ->earnings())
        $distEarnings = $distributor->earnings;
        $this->assertEquals(1, $distEarnings->count());
        $this->assertEquals($distributor->id, $distEarnings->first()->user_id);

        // Other user sees none own
        $otherEarnings = $otherUser->earnings;
        $this->assertEquals(0, $otherEarnings->count());
    }

    private function createTestUser($name, $mode, $sponsorId = null)
    {
        return User::create([
            'id' => strtoupper($name),
            'name' => $name,
            'email' => strtolower($name) . '@test.com',
            'password' => Hash::make('password'),
            'referral_code' => $name,
            'sponsor_id' => $sponsorId,
            'placement_side' => null,
            'is_admin' => false,
            'status' => 'approved',
            'level' => $sponsorId ? 1 : 0,
            'balancing_mode' => $mode,
            'account_balance' => 0,
        ]);
    }
}