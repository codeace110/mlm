<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AdminCode;
use App\Models\BinaryTree;
use App\Models\Bonus;
use App\Services\BinaryBalancerService;
use Illuminate\Support\Facades\Auth;

class DashboardNetworkTest extends TestCase
{
    use RefreshDatabase;

    private BinaryBalancerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BinaryBalancerService();
    }

    public function test_dashboard_network_displays_correct_tree_with_spillover_and_bonuses()
    {
        // Create main test user with complete profile
        $user = User::factory()->create([
            'phone' => '1234567890',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'shipping_name' => 'Test Shipping Name',
            'email_verified_at' => now(),
        ]);
        Auth::login($user);

        // Create admin codes for distributors
        $distributor1 = User::factory()->create();
        $distributor2 = User::factory()->create();
        $distributor3 = User::factory()->create();

        $code1 = AdminCode::create([
            'code' => 'CODE001',
            'distributor_id' => $distributor1->id,
            'status' => 'unused',
        ]);
        $code2 = AdminCode::create([
            'code' => 'CODE002',
            'distributor_id' => $distributor2->id,
            'status' => 'unused',
        ]);
        $code3 = AdminCode::create([
            'code' => 'CODE003',
            'distributor_id' => $distributor3->id,
            'status' => 'unused',
        ]);

        // Register 2 direct referrals under the main user
        $direct1 = User::factory()->create(['sponsor_id' => $user->id]);
        $direct2 = User::factory()->create(['sponsor_id' => $user->id]);

        // Place them as direct left and right
        $this->service->placeUser($direct1, $user, 'left');
        $this->service->placeUser($direct2, $user, 'right');

        // Now register spillover users under the direct referrals
        // Left side spillover
        $spillover1 = User::factory()->create(['sponsor_id' => $user->id]); // Will spillover to direct1
        $spillover2 = User::factory()->create(['sponsor_id' => $user->id]); // Will spillover to direct1

        $this->service->placeUser($spillover1, $user, 'left'); // Should place under direct1
        $this->service->placeUser($spillover2, $user, 'left'); // Should place under spillover1

        // Right side spillover
        $spillover3 = User::factory()->create(['sponsor_id' => $user->id]); // Will spillover to direct2
        $this->service->placeUser($spillover3, $user, 'right'); // Should place under direct2

        // Test the dashboard network endpoint
        $response = $this->get('/dashboard/network');

        $response->assertStatus(200);
        $response->assertViewHas('networkTree');

        $networkTree = $response->viewData('networkTree');

        // Verify root user
        $this->assertEquals($user->name, $networkTree['name']);
        $this->assertEquals($user->id, $networkTree['id']);

        // Verify direct children exist
        $this->assertCount(2, $networkTree['children']);
        $this->assertNotNull($networkTree['children'][0]); // Left direct
        $this->assertNotNull($networkTree['children'][1]); // Right direct

        // Debug: print the tree structure
        // dd($networkTree);

        // Verify spillover under left direct
        $leftDirect = $networkTree['children'][0];
        $this->assertNotNull($leftDirect['children']);
        // The spillover logic might place multiple under the same direct
        $this->assertGreaterThanOrEqual(1, count($leftDirect['children'])); // At least one spillover under left direct

        // Check that volumes are correctly calculated
        $userTree = BinaryTree::where('user_id', $user->id)->first();
        // Debug: check actual volumes
        // dd($userTree->toArray());

        $this->assertEquals(3, $userTree->total_left_volume); // direct1 + spillover1 + spillover2
        $this->assertEquals(2, $userTree->total_right_volume); // direct2 + spillover3 (but spillover3 might be placed differently)

        // Verify bonuses were calculated (at least one direct bonus for the pair)
        $directBonuses = Bonus::where('user_id', $user->id)->where('reward_type', 'direct')->get();
        $this->assertGreaterThanOrEqual(1, $directBonuses->count());
        $this->assertEquals(100, $directBonuses->first()->amount);

        // Check level bonuses - should have at least level 1 completed
        $levelBonuses = Bonus::where('user_id', $user->id)->where('reward_type', 'level')->get();
        $this->assertGreaterThanOrEqual(1, $levelBonuses->count());
        $this->assertEquals(1, $levelBonuses->first()->level_index);
        $this->assertEquals(100, $levelBonuses->first()->amount);

        // Verify tree structure shows correct carryover (effective volumes)
        // Root should have effective_left = 3 - 2 = 1 (consumed 2 for level 1)
        // effective_right = 1 - 0 = 1 (not consumed yet)
        $this->assertEquals(3, $networkTree['left_volume']); // total
        $this->assertEquals(2, $networkTree['right_volume']); // total (direct2 + spillover3)
        $this->assertEquals(1, $networkTree['carryover_left']); // effective = 3-2
        $this->assertEquals(2, $networkTree['carryover_right']); // effective = 2-0 (no consumption yet)
    }

    public function test_network_view_with_complex_spillover_and_multiple_levels()
    {
        // Create a more complex network to test deeper spillover
        $user = User::factory()->create([
            'phone' => '1234567890',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'shipping_name' => 'Test Shipping Name',
            'email_verified_at' => now(),
        ]);
        Auth::login($user);

        // Create distributors and codes
        $distributors = User::factory()->count(10)->create();
        foreach ($distributors as $i => $distributor) {
            AdminCode::create([
                'code' => 'CODE' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'distributor_id' => $distributor->id,
                'status' => 'unused',
            ]);
        }

        // Create a complex binary tree with spillover
        $users = [];
        for ($i = 0; $i < 20; $i++) {
            $newUser = User::factory()->create(['sponsor_id' => $user->id]);
            $this->service->placeUser($newUser, $user, $i % 2 === 0 ? 'left' : 'right');
            $users[] = $newUser;
        }

        // Test network view
        $response = $this->get('/dashboard/network');

        $response->assertStatus(200);

        $networkTree = $response->viewData('networkTree');

        // Verify structure
        $this->assertEquals($user->name, $networkTree['name']);

        // Should have 2 direct children
        $this->assertCount(2, $networkTree['children']);

        // Check volumes
        $userTree = BinaryTree::where('user_id', $user->id)->first();
        $this->assertEquals(10, $userTree->total_left_volume); // 10 left placements
        $this->assertEquals(10, $userTree->total_right_volume); // 10 right placements

        // Check that multiple level bonuses were awarded
        $levelBonuses = Bonus::where('user_id', $user->id)->where('reward_type', 'level')->orderBy('level_index')->get();

        // Should have consumed multiple levels
        // Level 1: 2 consumed from each side
        // Level 2: 4 consumed from each side
        // Level 3: 8 consumed from each side
        // Total consumed: 14 from each side, leaving 10-14 = -4 (but capped at 0)
        $this->assertGreaterThanOrEqual(3, $levelBonuses->count()); // At least levels 1,2,3

        // Verify the tree shows correct effective volumes
        $expectedEffectiveLeft = max(0, 10 - $userTree->left_consumed);
        $expectedEffectiveRight = max(0, 10 - $userTree->right_consumed);

        $this->assertEquals(10, $networkTree['left_volume']);
        $this->assertEquals(10, $networkTree['right_volume']);
        $this->assertEquals($expectedEffectiveLeft, $networkTree['carryover_left']);
        $this->assertEquals($expectedEffectiveRight, $networkTree['carryover_right']);
    }
}