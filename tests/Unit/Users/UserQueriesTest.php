<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Shared\Enums\SortDirection;
use App\Modules\Users\Data\UserIndexData;
use App\Modules\Users\Enums\UserSortBy;
use App\Modules\Users\Queries\UserQueries;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pagination uses a stable tie breaker when primary sort values match', function (): void {
    $users = User::factory()->count(3)->create(['name' => 'Same Name']);
    $query = new UserIndexData(page: 2, perPage: 1, search: 'Same Name', sortBy: UserSortBy::Name, sortDirection: SortDirection::Desc);
    $lengthAwarePaginator = new UserQueries()->paginate($query);
    expect($lengthAwarePaginator->getCollection()->modelKeys())->toBe([$users->get(1)?->id]);
});

test('paginate applies search sort and pagination', function (): void {
    User::factory()->create([
        'name' => 'Alpha Query',
        'email' => 'alpha-query@example.com',
        'role' => UserRole::User,
    ]);
    User::factory()->create([
        'name' => 'Zulu Query',
        'email' => 'zulu-query@example.com',
        'role' => UserRole::Admin,
    ]);
    User::factory()->create([
        'name' => 'Ignored Person',
        'email' => 'ignored@example.com',
        'role' => UserRole::User,
    ]);

    $lengthAwarePaginator = new UserQueries()->paginate(new UserIndexData(
        page: 1,
        perPage: 1,
        search: 'Query',
        sortBy: UserSortBy::Name,
        sortDirection: SortDirection::Desc,
    ));

    expect($lengthAwarePaginator->currentPage())->toBe(1);
    expect($lengthAwarePaginator->perPage())->toBe(1);
    expect($lengthAwarePaginator->total())->toBe(2);
    expect($lengthAwarePaginator->items())->toHaveCount(1);
    expect($lengthAwarePaginator->getCollection()->sole()->email)->toBe('zulu-query@example.com');
});
