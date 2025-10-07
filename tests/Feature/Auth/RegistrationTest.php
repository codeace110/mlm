<?php

use App\Providers\RouteServiceProvider;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $distributor = \App\Models\User::factory()->create();
    $adminCode = \App\Models\AdminCode::create([
        'code' => 'TESTADMIN123',
        'distributor_id' => $distributor->id,
        'status' => 'assigned',
    ]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'registration_code' => 'TESTADMIN123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(RouteServiceProvider::HOME);
});

test('registration fails with invalid admin code', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'registration_code' => 'INVALID123',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['registration_code']);
});

test('registration fails with used admin code', function () {
    $distributor = \App\Models\User::factory()->create();
    $adminCode = \App\Models\AdminCode::create([
        'code' => 'USEDADMIN123',
        'distributor_id' => $distributor->id,
        'status' => 'used',
    ]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'registration_code' => 'USEDADMIN123',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['registration_code']);
});
