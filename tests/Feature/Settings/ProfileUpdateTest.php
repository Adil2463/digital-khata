<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
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
test('profile information can be updated without losing verification', function (): void {
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
    expect($user->hasVerifiedEmail())->toBeTrue();
    $this->get('/app/dashboard')->assertOk();
});

test('changing your own login signs out other devices but keeps this session', function (): void {
    config(['session.driver' => 'database']);
    $user = User::factory()->create();
    $user->createToken('phone');
    DB::table('sessions')->insert(['id' => 'other-device', 'user_id' => $user->id, 'payload' => '', 'last_activity' => now()->getTimestamp()]);

    $this->actingAs($user)
        ->patch('/app/settings/profile', ['name' => $user->name, 'email' => 'new-login@shop.local'])
        ->assertRedirect('/app/settings/profile');

    expect(DB::table('sessions')->where('id', 'other-device')->exists())->toBeFalse()
        ->and($user->tokens()->count())->toBe(0);
    $this->get('/app/settings/profile')->assertOk();
});

test('renaming yourself keeps other devices signed in', function (): void {
    config(['session.driver' => 'database']);
    $user = User::factory()->create();
    DB::table('sessions')->insert(['id' => 'other-device', 'user_id' => $user->id, 'payload' => '', 'last_activity' => now()->getTimestamp()]);

    $this->actingAs($user)
        ->patch('/app/settings/profile', ['name' => 'Renamed', 'email' => $user->email])
        ->assertRedirect('/app/settings/profile');

    expect(DB::table('sessions')->where('id', 'other-device')->exists())->toBeTrue();
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

test('accounts cannot delete themselves', function (): void {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->delete('/app/settings/profile', ['password' => 'password'])
        ->assertMethodNotAllowed();

    expect($user->fresh())->not->toBeNull();
    $this->assertAuthenticatedAs($user);
    expect(Route::has('app.settings.profile.destroy'))->toBeFalse();
});
