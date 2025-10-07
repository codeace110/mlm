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
        $adminCode = AdminCode::factory()->create([
            'distributor_id' => $distributor->id,
            'status' => 'assigned',
        ]);

        // Debug: Check the admin code before registration
        $adminCode->refresh();
        $this->assertEquals('assigned', $adminCode->status);
        $this->assertNotNull($adminCode->distributor_id);

        // Debug: Check if code exists in database
        $existingCode = \App\Models\AdminCode::whereRaw('UPPER(code) = ?', [strtoupper($adminCode->code)])->first();
        $this->assertNotNull($existingCode, 'Admin code should exist in database');
        $this->assertEquals('assigned', $existingCode->status, 'Admin code should have assigned status');
        $this->assertNotNull($existingCode->distributor_id, 'Admin code should have distributor_id');

        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'registration_code' => $adminCode->code,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'sponsor_id' => $distributor->id,
            'registration_code' => $adminCode->code,
        ]);

        $adminCode->refresh();
        $this->assertEquals('used', $adminCode->status);
        $this->assertNotNull($adminCode->used_by_user_id);
    }

    /** @test */
    public function registration_fails_with_invalid_admin_code()
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'registration_code' => 'INVALID123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('registration_code');
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }

    /** @test */
    public function registration_fails_with_used_admin_code()
    {
        $distributor = User::factory()->create();
        $adminCode = AdminCode::factory()->create([
            'distributor_id' => $distributor->id,
            'status' => 'used',
        ]);

        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'registration_code' => $adminCode->code,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('registration_code');
    }

    /** @test */
    public function registration_creates_binary_tree()
    {
        $distributor = User::factory()->create();
        $adminCode = AdminCode::factory()->create([
            'distributor_id' => $distributor->id,
            'status' => 'assigned',
        ]);

        $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'registration_code' => $adminCode->code,
        ]);

        $newUser = User::where('email', 'new@example.com')->first();
        $this->assertDatabaseHas('binary_trees', ['user_id' => $newUser->id]);
    }
}