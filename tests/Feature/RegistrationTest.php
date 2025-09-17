<?php

namespace Tests\Feature;

use App\Models\AdminCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register_with_valid_admin_code()
    {
        $distributor = User::factory()->create();
        $code = AdminCode::factory()->create([
            'distributor_id' => $distributor->id,
            'status' => 'unused',
        ]);

        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'admin_code' => $code->code,
            'preferred_side' => 'left',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'sponsor_id' => $distributor->id,
            'placement_side' => 'left',
        ]);

        $code->refresh();
        $this->assertEquals('used', $code->status);
        $this->assertNotNull($code->used_by_user_id);
    }

    /** @test */
    public function registration_fails_with_invalid_admin_code()
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'admin_code' => 'INVALID',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('admin_code');
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }

    /** @test */
    public function registration_fails_with_used_admin_code()
    {
        $code = AdminCode::factory()->create(['status' => 'used']);

        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'admin_code' => $code->code,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('admin_code');
    }

    /** @test */
    public function registration_creates_binary_tree_and_bonuses()
    {
        $distributor = User::factory()->create();
        $code = AdminCode::factory()->create([
            'distributor_id' => $distributor->id,
            'status' => 'unused',
        ]);

        $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'admin_code' => $code->code,
        ]);

        $newUser = User::where('email', 'new@example.com')->first();
        $this->assertDatabaseHas('binary_trees', ['user_id' => $newUser->id]);
        $this->assertDatabaseHas('binary_trees', ['user_id' => $distributor->id]);
    }
}