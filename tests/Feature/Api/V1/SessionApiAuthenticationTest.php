<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('session.driver', 'database');
    config()->set('sanctum.stateful', ['localhost']);

    $user = User::factory()->create(['role' => UserRole::Admin]);
    $response = $this->post('/auth/login', ['email' => $user->email, 'password' => 'password']);
    $response->assertRedirect();

    $cookies = [];
    foreach ($response->headers->getCookies() as $cookie) {
        $cookies[$cookie->getName()] = $cookie->getValue();
    }

    // A fresh request must authenticate from the persisted cookie, not a test guard.
    Auth::forgetGuards();
    app('session')->forgetDrivers();
    app()->forgetInstance('session.store');
    app()->instance('env', 'local');

    $this->withUnencryptedCookies($cookies)->withCredentials()->withHeader('Origin', 'http://localhost');
});

test('browser session cookies authenticate api reads', function (): void {
    $this->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.role', UserRole::Admin->value);
});

test('browser api mutations accept the current encrypted xsrf cookie', function (): void {
    $this->postJson('/api/v1/admin/users', [
        'name' => 'Session User',
        'email' => 'session-user@example.com',
        'password' => 'Password123!@#',
        'role' => UserRole::User->value,
    ], ['X-XSRF-TOKEN' => $this->unencryptedCookies['XSRF-TOKEN'] ?? throw new RuntimeException('Login did not issue an XSRF cookie.')])
        ->assertCreated()->assertJsonPath('data.email', 'session-user@example.com');
});

test('browser api mutations reject missing or invalid csrf tokens', function (?string $token): void {
    $headers = $token === null ? [] : ['X-XSRF-TOKEN' => $token];
    $this->postJson('/api/v1/admin/users', [], $headers)->assertStatus(419);
})->with([null, 'invalid-token']);

test('untrusted origins cannot authenticate with a browser session cookie', function (): void {
    $this->withHeader('Origin', 'https://untrusted.example')->getJson('/api/v1/me')->assertUnauthorized();
});

test('external bearer clients authenticate without a session or csrf token', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('api-test')->plainTextToken;

    $this->withHeader('Origin', 'https://external.example')->withToken($token)
        ->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.id', $user->id);
});
