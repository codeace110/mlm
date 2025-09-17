<?php

namespace Tests\Feature;

use App\Models\AdminCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_access_admin_dashboard()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $response = $this->get('/admin');
        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $response = $this->get('/admin');
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_admin_codes()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        AdminCode::factory()->count(3)->create();
        $this->actingAs($admin);

        $response = $this->get('/admin/admin_codes');
        $response->assertStatus(200);
        $response->assertViewIs('admin.admin_codes.index');
    }

    /** @test */
    public function admin_can_assign_code_to_distributor()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $distributor = User::factory()->create();
        $code = AdminCode::factory()->create(['status' => 'issued']);
        $this->actingAs($admin);

        $response = $this->post("/admin/admin_codes/{$code->id}/assign", [
            'distributor_id' => $distributor->id,
        ]);

        $response->assertRedirect();
        $code->refresh();
        $this->assertEquals($distributor->id, $code->distributor_id);
        $this->assertEquals('unused', $code->status);
    }

    /** @test */
    public function admin_can_generate_admin_codes()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $response = $this->post('/admin/admin_codes/generate', [
            'count' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('admin_codes', 5);
    }

    /** @test */
    public function admin_can_view_users()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->count(3)->create();
        $this->actingAs($admin);

        $response = $this->get('/admin/users');
        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
    }

    /** @test */
    public function admin_can_approve_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['status' => 'pending']);
        $this->actingAs($admin);

        $response = $this->post("/admin/users/{$user->id}/approve");
        $response->assertRedirect();

        $user->refresh();
        $this->assertEquals('approved', $user->status);
    }
}