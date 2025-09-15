<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AdminCode;
use App\Services\AdminCodeService;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_with_valid_admin_code()
    {
        $distributor = User::factory()->create();
        $adminCode = AdminCode::create([
            'code' => 'TESTCODE123',
            'distributor_id' => $distributor->id,
            'status' => 'issued',
        ]);

        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'admin_code' => 'TESTCODE123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
        $adminCode->refresh();
        $this->assertEquals('used', $adminCode->status);
    }

    public function test_registration_with_invalid_admin_code()
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

    public function test_registration_requires_admin_code()
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('admin_code');
    }
}