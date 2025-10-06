<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Earning;
use App\Models\Withdrawal;
use App\Models\ReferralCode;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardService $dashboardService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dashboardService = new DashboardService();
    }

    /** @test */
    public function it_gets_dashboard_data_for_user()
    {
        $user = User::factory()->create([
            'account_balance' => 1500.00
        ]);

        // Create downlines
        $downline1 = User::factory()->create(['sponsor_id' => $user->id]);
        $downline2 = User::factory()->create(['sponsor_id' => $user->id]);

        // Create earnings
        Earning::create([
            'user_id' => $user->id,
            'amount' => 500.00,
            'type' => 'direct',
            'status' => 'pending'
        ]);
        Earning::create([
            'user_id' => $user->id,
            'amount' => 300.00,
            'type' => 'pair',
            'status' => 'completed'
        ]);

        // Create withdrawals
        Withdrawal::create([
            'user_id' => $user->id,
            'amount' => 200.00,
            'status' => 'pending'
        ]);

        // Create referral codes
        $referralCode = ReferralCode::create([
            'code' => 'TEST123',
            'assigned_to' => $user->id
        ]);

        $data = $this->dashboardService->getDashboardData($user);

        $this->assertEquals(2, $data['downlinesCount']);
        $this->assertEquals(800.00, $data['totalEarnings']);
        $this->assertEquals(500.00, $data['pendingEarnings']);
        $this->assertEquals(200.00, $data['totalWithdrawals']);
        $this->assertEquals(1, $data['pendingWithdrawals']);
        $this->assertEquals(1500.00, $data['accountBalance']);
        $this->assertCount(2, $data['recentReferrals']);
        $this->assertCount(2, $data['recentEarnings']);
        $this->assertCount(1, $data['referralCodes']);
        $this->assertEquals('TEST123', $data['referralCodes']->first()->code);
    }

    /** @test */
    public function it_gets_network_statistics_for_user()
    {
        $user = User::factory()->create();

        // Level 1 downlines
        $level1_1 = User::factory()->create(['sponsor_id' => $user->id]);
        $level1_2 = User::factory()->create(['sponsor_id' => $user->id]);

        // Level 2 downlines (under level1_1)
        $level2_1 = User::factory()->create(['sponsor_id' => $level1_1->id]);
        $level2_2 = User::factory()->create(['sponsor_id' => $level1_1->id]);

        // Level 3 downlines (under level2_1)
        $level3_1 = User::factory()->create(['sponsor_id' => $level2_1->id]);
        $level3_2 = User::factory()->create(['sponsor_id' => $level2_1->id]);
        $level3_3 = User::factory()->create(['sponsor_id' => $level2_1->id]);

        $stats = $this->dashboardService->getNetworkStatistics($user);

        $this->assertEquals(2, $stats['level1']);
        $this->assertEquals(2, $stats['level2']);
        $this->assertEquals(3, $stats['level3']);
        $this->assertEquals(7, $stats['total']);
    }

    /** @test */
    public function it_gets_earnings_chart_data_for_last_12_months()
    {
        $user = User::factory()->create();

        // Create earnings for different months
        $currentMonth = Carbon::now();
        $sixMonthsAgo = $currentMonth->copy()->subMonths(6);
        $elevenMonthsAgo = $currentMonth->copy()->subMonths(11);

        Earning::create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'type' => 'direct',
            'created_at' => $currentMonth
        ]);

        Earning::create([
            'user_id' => $user->id,
            'amount' => 200.00,
            'type' => 'pair',
            'created_at' => $sixMonthsAgo
        ]);

        Earning::create([
            'user_id' => $user->id,
            'amount' => 50.00,
            'type' => 'level',
            'created_at' => $elevenMonthsAgo
        ]);

        $chartData = $this->dashboardService->getEarningsChartData($user);

        $this->assertCount(12, $chartData['labels']);
        $this->assertCount(12, $chartData['data']);

        // Check that current month has 100
        $this->assertEquals(100.00, end($chartData['data']));

        // Check that 6 months ago has 200
        $sixMonthsIndex = 11 - 6; // 12 months total, current is last
        $this->assertEquals(200.00, $chartData['data'][$sixMonthsIndex]);

        // Check that 11 months ago has 50
        $this->assertEquals(50.00, $chartData['data'][0]);
    }

    /** @test */
    public function it_gets_network_chart_data_for_last_12_months()
    {
        $user = User::factory()->create();

        // Create downlines at different times
        $currentMonth = Carbon::now();
        $threeMonthsAgo = $currentMonth->copy()->subMonths(3);
        $sixMonthsAgo = $currentMonth->copy()->subMonths(6);

        User::factory()->create([
            'sponsor_id' => $user->id,
            'created_at' => $currentMonth
        ]);

        User::factory()->create([
            'sponsor_id' => $user->id,
            'created_at' => $threeMonthsAgo
        ]);

        User::factory()->create([
            'sponsor_id' => $user->id,
            'created_at' => $sixMonthsAgo
        ]);

        $chartData = $this->dashboardService->getNetworkChartData($user);

        $this->assertCount(12, $chartData['labels']);
        $this->assertCount(12, $chartData['data']);

        // Current month should have 3 downlines
        $this->assertEquals(3, end($chartData['data']));

        // 3 months ago should have 2 downlines
        $threeMonthsIndex = 11 - 3;
        $this->assertEquals(2, $chartData['data'][$threeMonthsIndex]);

        // 6 months ago should have 1 downline
        $sixMonthsIndex = 11 - 6;
        $this->assertEquals(1, $chartData['data'][$sixMonthsIndex]);
    }

    /** @test */
    public function it_gets_earnings_by_type_data()
    {
        $user = User::factory()->create();

        // Create earnings of different types
        Earning::create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'type' => 'direct'
        ]);

        Earning::create([
            'user_id' => $user->id,
            'amount' => 200.00,
            'type' => 'pair'
        ]);

        Earning::create([
            'user_id' => $user->id,
            'amount' => 150.00,
            'type' => 'direct'
        ]);

        Earning::create([
            'user_id' => $user->id,
            'amount' => 300.00,
            'type' => 'level'
        ]);

        $chartData = $this->dashboardService->getEarningsByTypeData($user);

        $this->assertCount(3, $chartData['labels']);
        $this->assertCount(3, $chartData['data']);

        // Check that direct earnings are summed correctly
        $directIndex = array_search('direct', $chartData['labels']);
        $this->assertEquals(250.00, $chartData['data'][$directIndex]);

        // Check that pair earnings are correct
        $pairIndex = array_search('pair', $chartData['labels']);
        $this->assertEquals(200.00, $chartData['data'][$pairIndex]);

        // Check that level earnings are correct
        $levelIndex = array_search('level', $chartData['labels']);
        $this->assertEquals(300.00, $chartData['data'][$levelIndex]);
    }

    /** @test */
    public function it_handles_user_with_no_data()
    {
        $user = User::factory()->create();

        $data = $this->dashboardService->getDashboardData($user);

        $this->assertEquals(0, $data['downlinesCount']);
        $this->assertEquals(0, $data['totalEarnings']);
        $this->assertEquals(0, $data['pendingEarnings']);
        $this->assertEquals(0, $data['totalWithdrawals']);
        $this->assertEquals(0, $data['pendingWithdrawals']);
        $this->assertEquals(0, $data['accountBalance']);
        $this->assertCount(0, $data['recentReferrals']);
        $this->assertCount(0, $data['recentEarnings']);
        $this->assertCount(0, $data['referralCodes']);
    }
}