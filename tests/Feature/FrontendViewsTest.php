<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendViewsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function home_page_loads_without_error()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('home');
    }

    /** @test */
    public function dashboard_loads_for_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertSee('Dashboard'); // Assuming view has this text
    }

    /** @test */
    public function referrals_page_loads_for_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/referrals');
        $response->assertStatus(200);
        $response->assertViewIs('referrals');
    }

    /** @test */
    public function earnings_page_loads_for_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/earnings');
        $response->assertStatus(200);
        $response->assertViewIs('earnings');
    }

    /** @test */
    public function network_tree_page_loads_for_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard/network');
        $response->assertStatus(200);
        $response->assertViewIs('DashboardNetwork');
        $response->assertSee('Network'); // Assuming view has network elements
    }

    /** @test */
    public function profile_page_loads_for_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard/profile');
        $response->assertStatus(200);
        $response->assertViewIs('DashboardProfile');
    }

    /** @test */
    public function admin_dashboard_loads_for_admin()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /** @test */
    public function admin_codes_index_loads_for_admin()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $response = $this->get('/admin/admin_codes');
        $response->assertStatus(200);
        $response->assertViewIs('admin.admin_codes.index');
    }

    /** @test */
    public function logout_button_appears_on_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard');
        $response->assertSee('Logout');
    }

    /** @test */
    public function get_started_button_appears_on_home_for_guest()
    {
        $response = $this->get('/');
        $response->assertSee('Get Started'); // Assuming home has this
    }

    /** @test */
    public function network_tree_shows_nodes()
    {
        $user = User::factory()->create();
        $direct1 = User::factory()->create(['sponsor_id' => $user->id]);
        $direct2 = User::factory()->create(['sponsor_id' => $user->id]);
        $this->actingAs($user);

        $response = $this->get('/dashboard/network');
        $response->assertSee($direct1->name);
        $response->assertSee($direct2->name);
    }
}