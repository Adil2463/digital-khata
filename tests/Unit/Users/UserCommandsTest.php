<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Users\Commands\UserCommands;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('create persists user and returns command result', function (): void {
    $userCommandResult = new UserCommands()->create(
        new CreateUserData(
            name: 'Created User',
            email: 'created-user@example.com',
            role: UserRole::User,
            password: 'Password123!@#',
        ),
    );

    $this->assertDatabaseHas('users', [
        'id' => $userCommandResult->user->id,
        'email' => 'created-user@example.com',
        'role' => UserRole::User->value,
    ]);
    expect($userCommandResult->changes)->toBe([]);
});
test('update persists changes and returns audited command result', function (): void {
    $user = User::factory()->create([
        'name' => 'Before Name',
        'email' => 'before@example.com',
        'role' => UserRole::User,
    ]);

    $userCommandResult = new UserCommands()->update(
        $user,
        new UpdateUserData(
            name: 'After Name',
            email: 'after@example.com',
            role: UserRole::Admin,
            password: 'Password456!@#',
        ),
    );

    $this->assertDatabaseHas('users', [
        'id' => $userCommandResult->user->id,
        'name' => 'After Name',
        'email' => 'after@example.com',
        'role' => UserRole::Admin->value,
    ]);
    expect($userCommandResult->changes)->toBe([
        'name' => ['before' => 'Before Name', 'after' => 'After Name'],
        'email' => ['before' => 'before@example.com', 'after' => 'after@example.com'],
        'role' => ['before' => 'user', 'after' => 'admin'],
        'password' => ['before' => '[REDACTED]', 'after' => '[REDACTED]'],
    ]);
    expect($user->fresh()?->email_verified_at)->toBeNull();
});

test('updating other user attributes preserves email verification', function (): void {
    $user = User::factory()->create();
    $verifiedAt = $user->email_verified_at;

    new UserCommands()->update($user, new UpdateUserData('New Name', $user->email, $user->role));

    expect($verifiedAt)->not->toBeNull()
        ->and($user->fresh()?->email_verified_at)->toEqual($verifiedAt);
});
test('delete removes user', function (): void {
    $user = User::factory()->create(['role' => UserRole::User]);

    new UserCommands()->delete($user);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
