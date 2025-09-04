<?php

use App\Providers\RouteServiceProvider;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $admin = \App\Models\User::factory()->create(['is_admin' => true]);
    $distributor = \App\Models\User::factory()->create();
    $service = new \App\Services\ReferralCodeService();
    $codes = $service->generateCodes($admin, 1);
    $code = $codes[0];
    $service->assignCodeToDistributor($code, $distributor);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'referral_code' => $code->code,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(RouteServiceProvider::HOME);
});
