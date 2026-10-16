<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Shared\Auth\DemoAccounts;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedDemoUsers(): void
{
    $migration = require database_path('migrations/0001_01_01_000000_create_users_table.php');
    $migration::seedUsers();
}

test('demo users are not seeded unless explicitly enabled', function (): void {
    expect(config('database.seed_users'))->toBeFalse();

    seedDemoUsers();

    expect(User::query()->exists())->toBeFalse();
});

test('demo users are never seeded in production even when enabled', function (): void {
    config(['database.seed_users' => true]);
    app()->detectEnvironment(fn (): string => 'production');

    seedDemoUsers();

    expect(User::query()->exists())->toBeFalse();
});

test('demo users can be seeded explicitly outside production', function (): void {
    config(['database.seed_users' => true]);

    seedDemoUsers();

    expect(User::query()->pluck('email')->sort()->values()->all())
        ->toBe(array_map(static fn (array $credential): string => $credential['email'], DemoAccounts::CREDENTIALS))
        ->and(DemoAccounts::usersWithPublishedPasswords())->toHaveCount(2);
});
