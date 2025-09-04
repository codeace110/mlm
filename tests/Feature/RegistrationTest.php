<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\ReferralCode;
use App\Services\ReferralCodeService;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_with_valid_referral_code()
    {
        $admin = User::factory()->create();
        $distributor = User::factory()->create();
        $service = new ReferralCodeService();

        $codes = $service->generateCodes($admin, 1);
        $code = $codes[0];
        $service->assignCodeToDistributor($code, $distributor);

        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'referral_code' => $code->code,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
        $code->refresh();
        $this->assertEquals('used', $code->status);
    }

    public function test_registration_with_invalid_referral_code()
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

    public function test_registration_requires_referral_code()
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('referral_code');
    }
}