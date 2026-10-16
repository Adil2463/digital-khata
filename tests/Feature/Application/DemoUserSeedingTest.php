<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Shared\Auth\DemoAccounts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

test('seeding works against the schema as it exists when the users migration runs', function (): void {
    config([
        'database.seed_users' => true,
        'database.connections.users_migration_only' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true],
    ]);
    $defaultConnection = DB::getDefaultConnection();
    DB::setDefaultConnection('users_migration_only');

    try {
        $migration = require database_path('migrations/0001_01_01_000000_create_users_table.php');
        $migration->up();

        expect(DB::table('users')->pluck('email')->sort()->values()->all())->toBe(['admin@app.com', 'user@app.com'])
            ->and(Schema::hasColumn('users', 'deactivated_at'))->toBeFalse();
    } finally {
        DB::setDefaultConnection($defaultConnection);
    }
});

test('demo users can be seeded explicitly outside production', function (): void {
    config(['database.seed_users' => true]);

    seedDemoUsers();

    expect(User::query()->pluck('email')->sort()->values()->all())
        ->toBe(array_map(static fn (array $credential): string => $credential['email'], DemoAccounts::CREDENTIALS))
        ->and(DemoAccounts::usersWithPublishedPasswords())->toHaveCount(2);
});
