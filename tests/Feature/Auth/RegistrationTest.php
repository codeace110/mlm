<?php

use App\Providers\RouteServiceProvider;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $distributor = \App\Models\User::factory()->create();
    $adminCode = \App\Models\AdminCode::create([
        'code' => 'TESTCODE123',
        'distributor_id' => $distributor->id,
        'status' => 'issued',
    ]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'admin_code' => 'TESTCODE123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(RouteServiceProvider::HOME);
});
