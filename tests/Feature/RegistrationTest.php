<?php

namespace Tests\Feature;

use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register_with_valid_referral_code()
    {
        $distributor = User::factory()->create();
        $code = ReferralCode::factory()->create([
            'generated_by' => $distributor->id,
            'assigned_to' => $distributor->id,
            'status' => 'assigned',
        ]);

        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'referral_code' => $code->code,
            'preferred_side' => 'left',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'sponsor_id' => $distributor->id,
        ]);

        $code->refresh();
        $this->assertEquals('used', $code->status);
        $this->assertNotNull($code->used_by);
    }

    /** @test */
    public function registration_fails_with_invalid_referral_code()
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'referral_code' => 'INVALID',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('referral_code');
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }

    /** @test */
    public function registration_fails_with_used_referral_code()
    {
        $code = ReferralCode::factory()->create(['status' => 'used']);

        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'referral_code' => $code->code,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('referral_code');
    }

    /** @test */
    public function registration_creates_binary_tree_and_bonuses()
    {
        $distributor = User::factory()->create();
        $code = ReferralCode::factory()->create([
            'generated_by' => $distributor->id,
            'assigned_to' => $distributor->id,
            'status' => 'assigned',
        ]);

        $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'referral_code' => $code->code,
        ]);

        $newUser = User::where('email', 'new@example.com')->first();
        $this->assertDatabaseHas('binary_trees', ['user_id' => $newUser->id]);
        $this->assertDatabaseHas('binary_trees', ['user_id' => $distributor->id]);
    }
}