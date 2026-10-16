<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('registration screen can be rendered', function (): void {
    $testResponse = $this->get('/auth/register');

    $testResponse->assertStatus(200);
});
test('new users can register', function (): void {
    $testResponse = $this->post('/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $testResponse->assertRedirect(route('app.dashboard', absolute: false));
    Auth::forgetGuards();
    $this->followingRedirects()->get('/app/dashboard')->assertOk()->assertViewHas('page.clearHistory', true);
});
