<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

const NEW_PASSWORD = 'New-Secret-Pass-9';

test('it sets a new password and revokes existing credentials', function (): void {
    Event::fake([PasswordReset::class]);
    config(['session.driver' => 'database']);
    $user = User::factory()->create(['email' => 'owner@shop.local']);
    $other = User::factory()->create();
    $user->createToken('device');
    DB::table('sessions')->insert([
        ['id' => 'owner-session', 'user_id' => $user->id, 'payload' => '', 'last_activity' => now()->getTimestamp()],
        ['id' => 'other-session', 'user_id' => $other->id, 'payload' => '', 'last_activity' => now()->getTimestamp()],
    ]);
    $rememberToken = $user->remember_token;

    $this->pendingArtisan('khata:user:reset-password', ['email' => ' Owner@Shop.Local '])
        ->expectsQuestion('New password', NEW_PASSWORD)
        ->expectsQuestion('Confirm new password', NEW_PASSWORD)
        ->doesntExpectOutputToContain(NEW_PASSWORD)
        ->expectsOutputToContain('Password updated for owner@shop.local')
        ->assertSuccessful();

    $user->refresh();

    expect(Hash::check(NEW_PASSWORD, $user->password))->toBeTrue()
        ->and(Hash::check('password', $user->password))->toBeFalse()
        ->and($user->remember_token)->not->toBe($rememberToken)
        ->and($user->tokens()->count())->toBe(0)
        ->and(DB::table('sessions')->pluck('id')->all())->toBe(['other-session']);

    Event::assertDispatched(PasswordReset::class, fn (PasswordReset $passwordReset): bool => $passwordReset->user->is($user));

    $this->post('/auth/login', ['email' => 'owner@shop.local', 'password' => NEW_PASSWORD]);
    $this->assertAuthenticatedAs($user);
});

test('it fails for an unknown login', function (): void {
    $this->pendingArtisan('khata:user:reset-password', ['email' => 'nobody@shop.local'])
        ->expectsOutputToContain('No account uses that login email')
        ->assertFailed();
});

test('it rejects invalid passwords and keeps the old one', function (string $password, string $confirmation, string $message): void {
    $user = User::factory()->create(['email' => 'owner@shop.local']);

    $this->pendingArtisan('khata:user:reset-password', ['email' => 'owner@shop.local'])
        ->expectsQuestion('New password', $password)
        ->expectsQuestion('Confirm new password', $confirmation)
        ->expectsOutputToContain($message)
        ->assertFailed();

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
})->with([
    'too short' => ['short1', 'short1', 'at least 10 characters'],
    'no letters' => ['1234567890', '1234567890', 'at least one letter'],
    'mismatched' => [NEW_PASSWORD, 'Something-Else-1', 'confirmation does not match'],
]);

test('it refuses to run without an interactive password prompt', function (): void {
    $user = User::factory()->create(['email' => 'owner@shop.local']);

    $this->pendingArtisan('khata:user:reset-password', ['email' => 'owner@shop.local', '--no-interaction' => true])
        ->assertFailed();

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});
