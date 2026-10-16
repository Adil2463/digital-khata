<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('public registration and email password reset routes do not exist', function (string $routeName): void {
    expect(Route::has($routeName))->toBeFalse();
})->with([
    'auth.register.create',
    'auth.register.store',
    'auth.password.request',
    'auth.password.email',
    'auth.password.reset',
    'auth.password.store',
]);

test('guests cannot reach registration or email reset endpoints', function (string $method, string $uri): void {
    $this->call($method, $uri, [
        'name' => 'Intruder',
        'email' => 'intruder@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'token' => 'token',
    ])->assertNotFound();

    $this->assertGuest();
    expect(User::query()->exists())->toBeFalse();
})->with([
    ['GET', '/auth/register'],
    ['POST', '/auth/register'],
    ['GET', '/auth/forgot-password'],
    ['POST', '/auth/forgot-password'],
    ['GET', '/auth/reset-password/token'],
    ['POST', '/auth/reset-password'],
]);

test('no password reset email is sent for an existing account', function (): void {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/auth/forgot-password', ['email' => $user->email])->assertNotFound();

    Notification::assertNotSentTo($user, ResetPassword::class);
});
