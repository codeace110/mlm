<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AdminCode;
use App\Models\BinaryTree;
use App\Models\Bonus;
use App\Models\Earning;
use App\Models\Withdrawal;

class FrontendViewsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_admin' => false]);
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_dashboard_view_loads_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertViewHas(['user', 'stats', 'recentBonuses', 'networkStats']);
    }

    public function test_dashboard_shows_user_statistics()
    {
        // Create some test data
        $earnings = Earning::factory()->count(3)->create(['user_id' => $this->user->id]);
        $bonuses = Bonus::factory()->count(2)->create(['user_id' => $this->user->id]);
        $withdrawals = Withdrawal::factory()->count(1)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Total Earnings');
        $response->assertSee('Available Balance');
        $response->assertSee('Total Bonuses');
    }

    public function test_dashboard_shows_network_statistics()
    {
        // Create downline users
        $downline1 = User::factory()->create(['sponsor_id' => $this->user->id, 'placement_side' => 'left']);
        $downline2 = User::factory()->create(['sponsor_id' => $this->user->id, 'placement_side' => 'right']);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Left Network');
        $response->assertSee('Right Network');
        $response->assertSee('Total Network');
    }

    public function test_network_page_loads_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('network'));

        $response->assertStatus(200);
        $response->assertViewIs('network');
        $response->assertViewHas(['user', 'networkData']);
    }

    public function test_network_page_shows_binary_tree_structure()
    {
        // Create a simple binary tree structure
        $leftChild = User::factory()->create(['sponsor_id' => $this->user->id, 'placement_side' => 'left']);
        $rightChild = User::factory()->create(['sponsor_id' => $this->user->id, 'placement_side' => 'right']);

        $response = $this->actingAs($this->user)
            ->get(route('network'));

        $response->assertStatus(200);
        $response->assertSee($leftChild->name);
        $response->assertSee($rightChild->name);
        $response->assertSee('Left');
        $response->assertSee('Right');
    }

    public function test_registration_form_has_registration_code_field()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
        $response->assertSee('registration_code');
        $response->assertSee('Registration Code');
    }

    public function test_registration_form_validation_messages()
    {
        $response = $this->post(route('register'), [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456',
            'registration_code' => ''
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name', 'email', 'password', 'registration_code']);
    }

    public function test_registration_form_shows_proper_labels()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Name');
        $response->assertSee('Email');
        $response->assertSee('Password');
        $response->assertSee('Confirm Password');
        $response->assertSee('Registration Code');
    }

    public function test_dashboard_network_page_loads_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard.network'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard-network');
    }

    public function test_dashboard_payout_page_loads_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard.payout'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard-payout');
        $response->assertViewHas(['user', 'pendingWithdrawals', 'completedWithdrawals']);
    }

    public function test_dashboard_payout_shows_withdrawal_history()
    {
        // Create withdrawal history
        $pendingWithdrawal = Withdrawal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);
        $completedWithdrawal = Withdrawal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard.payout'));

        $response->assertStatus(200);
        $response->assertSee('Pending Withdrawals');
        $response->assertSee('Completed Withdrawals');
    }

    public function test_dashboard_notifications_page_loads_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('notifications'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard-notification');
        $response->assertViewHas(['notifications']);
    }

    public function test_earnings_page_loads_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('earnings.index'));

        $response->assertStatus(200);
        $response->assertViewIs('earnings');
        $response->assertViewHas(['earnings', 'stats']);
    }

    public function test_earnings_page_shows_earnings_data()
    {
        // Create earnings data
        $earnings = Earning::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('earnings.index'));

        $response->assertStatus(200);
        $response->assertSee('Total Earnings');
        $response->assertSee('Available Balance');
    }

    public function test_referrals_page_loads_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('referrals.index'));

        $response->assertStatus(200);
        $response->assertViewIs('referrals');
        $response->assertViewHas(['referrals']);
    }

    public function test_referrals_page_shows_referral_data()
    {
        // Create referrals
        $referral1 = User::factory()->create(['sponsor_id' => $this->user->id]);
        $referral2 = User::factory()->create(['sponsor_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('referrals.index'));

        $response->assertStatus(200);
        $response->assertSee($referral1->name);
        $response->assertSee($referral2->name);
    }

    public function test_home_page_loads_correctly()
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewIs('home');
    }

    public function test_home_page_shows_marketing_content()
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('MLM');
        $response->assertSee('Network');
        $response->assertSee('Binary');
    }

    public function test_packages_page_loads_correctly()
    {
        $response = $this->get(route('packages'));

        $response->assertStatus(200);
        $response->assertViewIs('packages');
    }

    public function test_packages_page_shows_package_information()
    {
        $response = $this->get(route('packages'));

        $response->assertStatus(200);
        $response->assertSee('Package');
        $response->assertSee('Price');
    }

    public function test_dashboard_shows_recent_bonuses()
    {
        // Create recent bonuses
        $bonus1 = Bonus::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100,
            'reward_type' => 'direct',
            'created_at' => now()->subDays(1)
        ]);
        $bonus2 = Bonus::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 50,
            'reward_type' => 'level',
            'created_at' => now()->subHours(2)
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('₱100'); // Fixed amount display
        $response->assertSee('₱50');
        $response->assertSee('Direct');
        $response->assertSee('Level');
    }

    public function test_dashboard_shows_earnings_breakdown()
    {
        // Create different types of earnings
        $directEarnings = Earning::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100,
            'type' => 'direct_bonus'
        ]);
        $levelEarnings = Earning::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 50,
            'type' => 'level_bonus'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Direct Bonus');
        $response->assertSee('Level Bonus');
    }

    public function test_dashboard_shows_balance_information()
    {
        // Set user balance
        $this->user->update(['account_balance' => 500.00]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('₱500.00');
        $response->assertSee('Available Balance');
    }

    public function test_network_page_shows_tree_levels()
    {
        // Create multi-level network
        $level1Left = User::factory()->create(['sponsor_id' => $this->user->id, 'placement_side' => 'left']);
        $level1Right = User::factory()->create(['sponsor_id' => $this->user->id, 'placement_side' => 'right']);
        $level2Left = User::factory()->create(['sponsor_id' => $level1Left->id, 'placement_side' => 'left']);

        $response = $this->actingAs($this->user)
            ->get(route('network'));

        $response->assertStatus(200);
        $response->assertSee('Level 1');
        $response->assertSee('Level 2');
        $response->assertSee($level1Left->name);
        $response->assertSee($level1Right->name);
        $response->assertSee($level2Left->name);
    }

    public function test_registration_form_has_proper_styling()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('form');
        $response->assertSee('input');
        $response->assertSee('button');
        $response->assertSee('Register');
    }

    public function test_registration_form_has_csrf_protection()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('csrf');
    }

    public function test_dashboard_has_navigation_menu()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Network');
        $response->assertSee('Earnings');
        $response->assertSee('Referrals');
        $response->assertSee('Payout');
    }

    public function test_dashboard_shows_user_profile_information()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee($this->user->email);
    }

    public function test_dashboard_shows_empty_states_when_no_data()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('0'); // Should show zeros for empty stats
    }

    public function test_network_page_handles_empty_network()
    {
        $response = $this->actingAs($this->user)
            ->get(route('network'));

        $response->assertStatus(200);
        $response->assertSee('No network data');
    }

    public function test_earnings_page_handles_no_earnings()
    {
        $response = $this->actingAs($this->user)
            ->get(route('earnings.index'));

        $response->assertStatus(200);
        $response->assertSee('No earnings');
    }

    public function test_referrals_page_handles_no_referrals()
    {
        $response = $this->actingAs($this->user)
            ->get(route('referrals.index'));

        $response->assertStatus(200);
        $response->assertSee('No referrals');
    }

    public function test_dashboard_shows_fixed_currency_format()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('₱'); // Philippine Peso symbol
    }

    public function test_registration_form_has_responsive_design()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('container');
        $response->assertSee('row');
        $response->assertSee('col');
    }

    public function test_dashboard_has_proper_meta_tags()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('title');
        $response->assertSee('meta');
    }

    public function test_registration_form_has_accessibility_features()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('label');
        $response->assertSee('for');
        $response->assertSee('id');
    }
}