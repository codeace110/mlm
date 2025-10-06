<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_loads_for_authenticated_user()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'address' => 'Test Address',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertViewHas(['user', 'networkStats', 'earningsByType']);
    }

    public function test_dashboard_redirects_to_onboarding_for_incomplete_profile()
    {
        $user = User::factory()->create([
            'phone' => null, // Incomplete profile
            'address' => 'Test Address',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/onboarding');
    }

    public function test_dashboard_network_page_loads()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'address' => 'Test Address',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard/network');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard-network');
    }

    public function test_dashboard_payout_page_loads()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'address' => 'Test Address',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard/payout');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard-payout');
    }

    public function test_home_page_loads_for_guest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('home');
    }

    public function test_login_page_loads()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_register_page_loads()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    public function test_admin_dashboard_requires_admin_role()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(403); // Forbidden
    }

    public function test_admin_dashboard_loads_for_admin()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }
}