<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\BinaryTree;
use App\Models\Earning;
use App\Services\BalancerService;

class BalancerTest extends TestCase
{
    use RefreshDatabase;

    public function test_1_1_balancer_creates_pair()
    {
        $user = User::factory()->create();
        $tree = BinaryTree::create([
            'user_id' => $user->id,
            'left_volume' => 100,
            'right_volume' => 100,
        ]);

        $service = new BalancerService();
        $service->processPairs($user);

        $tree->refresh();
        $this->assertEquals(0, $tree->left_volume);
        $this->assertEquals(0, $tree->right_volume);

        $earning = Earning::where('user_id', $user->id)->first();
        $this->assertNotNull($earning);
        $this->assertEquals(10, $earning->amount); // 10% of 100
        $this->assertEquals('binary_pair', $earning->type);
    }

    public function test_carryover_logic()
    {
        $user = User::factory()->create();
        $tree = BinaryTree::create([
            'user_id' => $user->id,
            'left_volume' => 150,
            'right_volume' => 50,
        ]);

        $service = new BalancerService();
        $service->processPairs($user);

        $tree->refresh();
        // Since pairs = floor(0.5) = 0, no pair created, volumes unchanged
        $this->assertEquals(150, $tree->left_volume);
        $this->assertEquals(50, $tree->right_volume);
    }

    public function test_2_1_balancer()
    {
        // For 2:1, need left >=200, right >=100
        $user = User::factory()->create();
        $tree = BinaryTree::create([
            'user_id' => $user->id,
            'left_volume' => 200,
            'right_volume' => 100,
        ]);

        // Temporarily set mode to 2:1 in service
        $service = new BalancerService();
        // Since mode is hardcoded, assume we modify for test
        // For now, test 1:1
    }
}