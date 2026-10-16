<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('unverified users reach the namespaced verification prompt from protected pages', function (string $path): void {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user)->get($path)->assertRedirect(route('auth.verification.notice'));
    $this->getJson($path)->assertForbidden();
})->with(['/app/dashboard', '/app/admin/users']);

test('email verification screen can be rendered', function (): void {
    $user = User::factory()->unverified()->create();

    $testResponse = $this->actingAs($user)->get('/auth/verify-email');

    $testResponse
        ->assertStatus(200)
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->where('status', null)
        );
});
test('email can be verified', function (): void {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'auth.verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $testResponse = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    $freshUser = $user->fresh();
    expect($freshUser?->hasVerifiedEmail())->toBeTrue();

    $testResponse->assertRedirect(route('app.dashboard', absolute: false).'?verified=1');
});
test('email is not verified with invalid hash', function (): void {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'auth.verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    $freshUser = $user->fresh();
    expect($freshUser?->hasVerifiedEmail())->toBeFalse();
});
