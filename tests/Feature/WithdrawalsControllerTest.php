<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'account_balance' => 1000.00,
            'phone' => '1234567890',
            'address' => 'Test Address',
            'email_verified_at' => now(),
        ]);
    }

    public function test_store_withdrawal_request_successfully()
    {
        $this->actingAs($this->user);

        $data = [
            'amount' => 500.00,
            'method' => 'gcash',
            'account_details' => [
                'account_number' => '09123456789',
                'account_name' => 'Test User',
            ],
        ];

        $response = $this->post(route('withdrawals.store'), $data);

        $response->assertRedirect(route('dashboard.payout'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('withdrawals', [
            'user_id' => $this->user->id,
            'amount' => 500.00,
            'method' => 'gcash',
            'status' => 'pending',
        ]);
    }

    public function test_store_withdrawal_fails_insufficient_balance()
    {
        $this->actingAs($this->user);

        $data = [
            'amount' => 1500.00, // More than balance
            'method' => 'gcash',
            'account_details' => [
                'account_number' => '09123456789',
                'account_name' => 'Test User',
            ],
        ];

        $response = $this->post(route('withdrawals.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHasErrors('amount');

        $this->assertDatabaseMissing('withdrawals', [
            'user_id' => $this->user->id,
            'amount' => 1500.00,
        ]);
    }

    public function test_store_withdrawal_fails_incomplete_profile()
    {
        $user = User::factory()->create([
            'account_balance' => 1000.00,
            'phone' => null, // Missing phone
            'address' => 'Test Address',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $data = [
            'amount' => 500.00,
            'method' => 'gcash',
            'account_details' => [
                'account_number' => '09123456789',
                'account_name' => 'Test User',
            ],
        ];

        $response = $this->post(route('withdrawals.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHasErrors('phone');
    }

    public function test_store_withdrawal_fails_validation()
    {
        $this->actingAs($this->user);

        $data = [
            'amount' => 100.00, // Below minimum
            'method' => 'invalid_method',
            'account_details' => 'not_an_array',
        ];

        $response = $this->post(route('withdrawals.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['amount', 'method', 'account_details']);
    }

    public function test_ajax_stats_returns_correct_data()
    {
        $this->actingAs($this->user);

        // Create some withdrawals
        Withdrawal::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100.00,
            'status' => 'approved',
        ]);

        Withdrawal::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 200.00,
            'status' => 'pending',
        ]);

        Withdrawal::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 50.00,
            'status' => 'denied',
        ]);

        $response = $this->getJson(route('withdrawals.ajax.stats'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'stats' => [
                'total' => 350.00,
                'pending' => 200.00,
                'approved' => 100.00,
                'denied_count' => 1,
                'available_balance' => 1000.00,
            ],
        ]);
    }

    public function test_ajax_recent_returns_recent_withdrawals()
    {
        $this->actingAs($this->user);

        Withdrawal::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson(route('withdrawals.ajax.recent', ['limit' => 2]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertCount(2, $response->json('withdrawals'));
    }

    public function test_dashboard_displays_withdrawals()
    {
        $this->actingAs($this->user);

        Withdrawal::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('dashboard.payout'));

        $response->assertStatus(200);
        $response->assertViewHas(['withdrawals', 'stats', 'user', 'paymentMethods']);
    }

    public function test_dashboard_filters_by_status()
    {
        $this->actingAs($this->user);

        Withdrawal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        Withdrawal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'approved',
        ]);

        $response = $this->get(route('dashboard.payout', ['status' => 'pending']));

        $response->assertStatus(200);
        $withdrawals = $response->viewData('withdrawals');
        $this->assertCount(1, $withdrawals);
        $this->assertEquals('pending', $withdrawals->first()->status);
    }

    public function test_dashboard_filters_by_method()
    {
        $this->actingAs($this->user);

        Withdrawal::factory()->create([
            'user_id' => $this->user->id,
            'method' => 'gcash',
        ]);

        Withdrawal::factory()->create([
            'user_id' => $this->user->id,
            'method' => 'palawan_pawnshop',
        ]);

        $response = $this->get(route('dashboard.payout', ['method' => 'gcash']));

        $response->assertStatus(200);
        $withdrawals = $response->viewData('withdrawals');
        $this->assertCount(1, $withdrawals);
        $this->assertEquals('gcash', $withdrawals->first()->method);
    }

    public function test_dashboard_filters_by_date_range()
    {
        $this->actingAs($this->user);

        Withdrawal::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(10),
        ]);

        Withdrawal::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now(),
        ]);

        $response = $this->get(route('dashboard.payout', [
            'start_date' => now()->subDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(1)->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $withdrawals = $response->viewData('withdrawals');
        $this->assertCount(1, $withdrawals);
    }

    public function test_store_requires_authentication()
    {
        $data = [
            'amount' => 500.00,
            'method' => 'gcash',
            'account_details' => [
                'account_number' => '09123456789',
                'account_name' => 'Test User',
            ],
        ];

        $response = $this->post(route('withdrawals.store'), $data);

        $response->assertRedirect(route('login'));
    }
}