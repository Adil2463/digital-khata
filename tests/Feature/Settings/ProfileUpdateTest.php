<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('profile page is displayed', function (): void {
    $user = User::factory()->create();

    $testResponse = $this
        ->actingAs($user)
        ->get('/app/settings/profile');

    $testResponse
        ->assertOk()
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->where('mustVerifyEmail', true)
            ->where('status', null)
            ->where('auth.user.email_verified_at', $user->email_verified_at?->format(config()->string('data.date_format')))
        );
});
test('profile information can be updated', function (): void {
    $user = User::factory()->create();

    $testResponse = $this
        ->actingAs($user)
        ->patch('/app/settings/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $testResponse
        ->assertSessionHasNoErrors()
        ->assertRedirect('/app/settings/profile');

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
    $this->get('/app/dashboard')->assertRedirect(route('auth.verification.notice'));
});
test('email verification status is unchanged when the email address is unchanged', function (): void {
    $user = User::factory()->create();

    $testResponse = $this
        ->actingAs($user)
        ->patch('/app/settings/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $testResponse
        ->assertSessionHasNoErrors()
        ->assertRedirect('/app/settings/profile');

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('email verification status can be explicitly provided when updating email address', function (): void {
    $user = User::factory()->create();
    $timestamp = now()->subDay();

    $user->update([
        'email' => 'explicit@example.com',
        'email_verified_at' => $timestamp,
    ]);

    $fresh = $user->fresh();
    expect($fresh?->email)->toBe('explicit@example.com');
    expect($fresh?->email_verified_at?->timestamp)->toBe($timestamp->timestamp);
});
test('user can delete their account', function (): void {
    $user = User::factory()->create();

    $testResponse = $this
        ->actingAs($user)
        ->delete('/app/settings/profile', [
            'password' => 'password',
        ]);

    $testResponse
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
    $this->get('/')->assertViewHas('page.clearHistory', true);
});
test('correct password must be provided to delete account', function (): void {
    $user = User::factory()->create();

    $testResponse = $this
        ->actingAs($user)
        ->from('/app/settings/profile')
        ->delete('/app/settings/profile', [
            'password' => 'wrong-password',
        ]);

    $testResponse
        ->assertSessionHasErrors('password')
        ->assertRedirect('/app/settings/profile');

    expect($user->fresh())->not->toBeNull();
});
