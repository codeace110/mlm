<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Earning;
use App\Models\ReferralCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function dashboard_index_requires_authenticated_user()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function dashboard_index_redirects_to_onboarding_if_profile_incomplete()
    {
        $user = User::factory()->create([
            'phone' => null,
            'address' => null,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertRedirect('/onboarding');
    }

    /** @test */
    public function dashboard_index_displays_correct_data_for_complete_user()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'shipping_name' => 'Test Shipping',
            'account_balance' => 1500.00,
            'email_verified_at' => now(),
        ]);

        // Create some test data
        $downline1 = User::factory()->create(['sponsor_id' => $user->id]);
        $downline2 = User::factory()->create(['sponsor_id' => $user->id]);

        Earning::create([
            'user_id' => $user->id,
            'amount' => 500.00,
            'type' => 'direct',
            'status' => 'completed'
        ]);

        ReferralCode::create([
            'code' => 'TEST123',
            'assigned_to' => $user->id
        ]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('user');
        $response->assertViewHas('downlinesCount', 2);
        $response->assertViewHas('totalEarnings', 500.00);
        $response->assertViewHas('accountBalance', 1500.00);
    }

    /** @test */
    public function dashboard_network_requires_authenticated_user()
    {
        $response = $this->get('/network');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function dashboard_network_displays_network_tree()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'shipping_name' => 'Test Shipping',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/network');

        $response->assertStatus(200);
        $response->assertViewHas('networkTree');
    }

    /** @test */
    public function ajax_chart_data_requires_authenticated_user()
    {
        $response = $this->get('/ajax/dashboard/charts');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function ajax_chart_data_returns_correct_json_structure()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'shipping_name' => 'Test Shipping',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/ajax/dashboard/charts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'earnings' => [
                'labels',
                'data'
            ],
            'network' => [
                'labels',
                'data'
            ]
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertCount(12, $data['earnings']['labels']); // 12 months
        $this->assertCount(12, $data['earnings']['data']);
        $this->assertCount(12, $data['network']['labels']);
        $this->assertCount(12, $data['network']['data']);
    }

    /** @test */
    public function ajax_earnings_by_type_requires_authenticated_user()
    {
        $response = $this->get('/ajax/dashboard/earnings-by-type');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function ajax_earnings_by_type_returns_correct_data()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'shipping_name' => 'Test Shipping',
            'email_verified_at' => now(),
        ]);

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

        $this->actingAs($user);

        $response = $this->get('/ajax/dashboard/earnings-by-type');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'labels',
            'data'
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);

        // Should have direct and pair types
        $this->assertContains('direct', $data['labels']);
        $this->assertContains('pair', $data['labels']);

        // Check that direct earnings are summed correctly
        $directIndex = array_search('direct', $data['labels']);
        $this->assertEquals(250.00, $data['data'][$directIndex]);

        $pairIndex = array_search('pair', $data['labels']);
        $this->assertEquals(200.00, $data['data'][$pairIndex]);
    }

    /** @test */
    public function dashboard_notification_requires_authenticated_user()
    {
        $response = $this->get('/notifications');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function dashboard_notification_creates_sample_notifications_if_none_exist()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'shipping_name' => 'Test Shipping',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/notifications');

        $response->assertStatus(200);
        $response->assertViewHas('notifications');

        // Should have created sample notifications
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'success',
            'title' => 'Welcome to AKEN MLM!'
        ]);
    }

    /** @test */
    public function dashboard_notification_displays_existing_notifications()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'shipping_name' => 'Test Shipping',
            'email_verified_at' => now(),
        ]);

        // Create a notification manually
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'type' => 'info',
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
            'icon' => 'info',
            'color' => 'primary',
            'is_read' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get('/notifications');

        $response->assertStatus(200);
        $response->assertViewHas('notifications');

        $notifications = $response->viewData('notifications');
        $this->assertCount(1, $notifications);
        $this->assertEquals('Test Notification', $notifications->first()->title);
    }
}