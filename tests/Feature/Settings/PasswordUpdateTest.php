<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

const UPDATED_PASSWORD = 'Counter-Pass-2026';

test('password can be updated', function (): void {
    $user = User::factory()->create();

    $testResponse = $this
        ->actingAs($user)
        ->from('/app/settings/password')
        ->put('/app/settings/password', [
            'current_password' => 'password',
            'password' => UPDATED_PASSWORD,
            'password_confirmation' => UPDATED_PASSWORD,
        ]);

    $testResponse
        ->assertSessionHasNoErrors()
        ->assertRedirect('/app/settings/password');

    expect(Hash::check(UPDATED_PASSWORD, $user->refresh()->password))->toBeTrue();
});

test('changing your password signs out other devices but keeps this session', function (): void {
    config(['session.driver' => 'database']);
    $user = User::factory()->create();
    $user->createToken('phone');
    DB::table('sessions')->insert(['id' => 'other-device', 'user_id' => $user->id, 'payload' => '', 'last_activity' => now()->getTimestamp()]);

    $this->actingAs($user)
        ->from('/app/settings/password')
        ->put('/app/settings/password', [
            'current_password' => 'password',
            'password' => UPDATED_PASSWORD,
            'password_confirmation' => UPDATED_PASSWORD,
        ])
        ->assertRedirect('/app/settings/password');

    expect(DB::table('sessions')->where('id', 'other-device')->exists())->toBeFalse()
        ->and($user->tokens()->count())->toBe(0);
    $this->get('/app/settings/password')->assertOk();
});

test('correct password must be provided to update password', function (): void {
    $user = User::factory()->create();

    $testResponse = $this
        ->actingAs($user)
        ->from('/app/settings/password')
        ->put('/app/settings/password', [
            'current_password' => 'wrong-password',
            'password' => UPDATED_PASSWORD,
            'password_confirmation' => UPDATED_PASSWORD,
        ]);

    $testResponse
        ->assertSessionHasErrors('current_password')
        ->assertRedirect('/app/settings/password');
});

test('new passwords must be strong', function (string $password, string $message): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/app/settings/password')
        ->put('/app/settings/password', [
            'current_password' => 'password',
            'password' => $password,
            'password_confirmation' => $password,
        ])
        ->assertSessionHasErrors(['password' => $message]);

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
})->with([
    'too short' => ['abc12345', 'The password field must be at least 10 characters.'],
    'no numbers' => ['only-letters-here', 'The password field must contain at least one number.'],
    'no letters' => ['1234567890', 'The password field must contain at least one letter.'],
]);
