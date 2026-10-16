<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Shop;
use App\Models\User;
use App\Modules\Users\Commands\UserCommands;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function userCommands(): UserCommands
{
    return app(UserCommands::class);
}

test('create places the account in the owner shop and marks it verified', function (): void {
    $owner = User::factory()->owner()->create();
    Shop::factory()->create();

    $userCommandResult = userCommands()->create(
        new CreateUserData(name: 'Created User', email: 'created-user@shop.local', role: UserRole::User, password: 'Password123'),
        $owner,
    );

    $user = $userCommandResult->user->refresh();

    expect($user->shop_id)->toBe($owner->shop_id)
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->deactivated_at)->toBeNull()
        ->and($user->role)->toBe(UserRole::User)
        ->and(Hash::check('Password123', $user->password))->toBeTrue()
        ->and($userCommandResult->changes)->toBe([]);
});

test('create refuses an owner without a shop', function (): void {
    $owner = User::factory()->owner()->withoutShop()->create();

    expect(fn (): mixed => userCommands()->create(new CreateUserData('X', 'x@shop.local', UserRole::User, 'Password123'), $owner))
        ->toThrow(RuntimeException::class);
});

test('update persists changes, keeps verification, and audits without the password', function (): void {
    $owner = User::factory()->owner()->create();
    $user = User::factory()->unverified()->create(['name' => 'Before Name', 'email' => 'before@shop.local']);

    $userCommandResult = userCommands()->update(
        $user,
        new UpdateUserData(name: 'After Name', email: 'after@shop.local', role: UserRole::Admin, password: 'Password456'),
        $owner,
    );

    expect($user->fresh()?->only(['name', 'email']))->toBe(['name' => 'After Name', 'email' => 'after@shop.local'])
        ->and($user->fresh()?->hasVerifiedEmail())->toBeTrue()
        ->and($userCommandResult->changes)->toBe([
            'name' => ['before' => 'Before Name', 'after' => 'After Name'],
            'email' => ['before' => 'before@shop.local', 'after' => 'after@shop.local'],
            'role' => ['before' => 'user', 'after' => 'admin'],
            'password' => ['before' => '[REDACTED]', 'after' => '[REDACTED]'],
        ]);
});

test('renaming alone keeps sessions and tokens', function (): void {
    config(['session.driver' => 'database']);
    $owner = User::factory()->owner()->create();
    $user = User::factory()->create();
    $user->createToken('device');
    DB::table('sessions')->insert(['id' => 'staff-session', 'user_id' => $user->id, 'payload' => '', 'last_activity' => now()->getTimestamp()]);

    userCommands()->update($user, new UpdateUserData('New Name', $user->email, $user->role), $owner);

    expect($user->tokens()->count())->toBe(1)
        ->and(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(1);
});

test('an owner cannot remove their own owner role', function (): void {
    $owner = User::factory()->owner()->create();
    User::factory()->owner()->create();

    expect(fn (): mixed => userCommands()->update($owner, new UpdateUserData($owner->name, $owner->email, UserRole::User), $owner))
        ->toThrow(ValidationException::class, 'You cannot remove your own Owner role.');

    expect($owner->fresh()?->role)->toBe(UserRole::Admin);
});

test('the last active owner cannot be demoted', function (): void {
    $actingOwner = User::factory()->owner()->deactivated()->create();
    $lastActiveOwner = User::factory()->owner()->create();

    expect(fn (): mixed => userCommands()->update($lastActiveOwner, new UpdateUserData($lastActiveOwner->name, $lastActiveOwner->email, UserRole::User), $actingOwner))
        ->toThrow(ValidationException::class, 'The shop must keep at least one active owner.');
});

test('another owner can be demoted while an active owner remains', function (): void {
    $owner = User::factory()->owner()->create();
    $secondOwner = User::factory()->owner()->create();

    userCommands()->update($secondOwner, new UpdateUserData($secondOwner->name, $secondOwner->email, UserRole::User), $owner);

    expect($secondOwner->fresh()?->role)->toBe(UserRole::User);
});

test('deactivate marks the account and revokes its credentials', function (): void {
    config(['session.driver' => 'database']);
    $owner = User::factory()->owner()->create();
    $staff = User::factory()->create();
    $staff->createToken('device');
    DB::table('sessions')->insert(['id' => 'staff-session', 'user_id' => $staff->id, 'payload' => '', 'last_activity' => now()->getTimestamp()]);
    $rememberToken = $staff->remember_token;

    $userCommandResult = userCommands()->deactivate($staff, $owner);

    $staff->refresh();

    expect($staff->deactivated_at)->not->toBeNull()
        ->and($staff->canSignIn())->toBeFalse()
        ->and($staff->remember_token)->not->toBe($rememberToken)
        ->and($staff->tokens()->count())->toBe(0)
        ->and(DB::table('sessions')->where('user_id', $staff->id)->exists())->toBeFalse()
        ->and($userCommandResult->changes)->toBe(['status' => ['before' => 'active', 'after' => 'deactivated']]);
    $this->assertDatabaseHas('users', ['id' => $staff->id]);
});

test('an owner cannot deactivate themself', function (): void {
    $owner = User::factory()->owner()->create();
    User::factory()->owner()->create();

    expect(fn (): mixed => userCommands()->deactivate($owner, $owner))
        ->toThrow(ValidationException::class, 'You cannot deactivate your own account.');
});

test('the last active owner cannot be deactivated', function (): void {
    $deactivatedOwner = User::factory()->owner()->deactivated()->create();
    $lastActiveOwner = User::factory()->owner()->create();

    expect(fn (): mixed => userCommands()->deactivate($lastActiveOwner, $deactivatedOwner))
        ->toThrow(ValidationException::class, 'The shop must keep at least one active owner.');

    expect($lastActiveOwner->fresh()?->deactivated_at)->toBeNull();
});

test('reactivate restores sign in', function (): void {
    $staff = User::factory()->deactivated()->create();

    userCommands()->reactivate($staff);

    expect($staff->fresh()?->canSignIn())->toBeTrue();
});
