<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('guests are redirected from user management routes', function (): void {
    $testResponse = $this->get('/app/admin/users');

    $testResponse->assertRedirect('/auth/login');
});
test('guests are redirected from user management routes even when reverb app id is missing', function (): void {
    config()->set('broadcasting.default', 'reverb');
    config()->set('broadcasting.connections.reverb.key', 'local-key');
    config()->set('broadcasting.connections.reverb.secret', 'local-secret');
    config()->set('broadcasting.connections.reverb.app_id', '');

    Broadcast::forgetDrivers();

    $testResponse = $this->get('/app/admin/users');

    $testResponse->assertRedirect('/auth/login');
});
test('non admin users cannot access user listing', function (): void {
    $user = User::factory()->create(['role' => UserRole::User]);

    $testResponse = $this->actingAs($user)->get('/app/admin/users');

    $testResponse->assertForbidden();
});
test('admin users can view user listing', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $testResponse = $this->actingAs($admin)->get('/app/admin/users');

    $testResponse->assertOk();
});
test('admin users can create users', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $testResponse = $this->actingAs($admin)->post('/app/admin/users', [
        'name' => 'New User',
        'email' => 'new-user@example.com',
        'password' => 'Password123!@#',
        'role' => UserRole::User->value,
    ]);

    $testResponse->assertRedirect('/app/admin/users');
    $this->assertDatabaseHas('users', [
        'email' => 'new-user@example.com',
        'role' => UserRole::User->value,
    ]);
});

test('empty user pages still carry the shared pagination envelope', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get('/app/admin/users?search=nonexistent-user')
        ->assertOk()
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->has('items', 0)
            ->where('pagination.current_page', 1)
            ->where('pagination.last_page', 1)
            ->where('pagination.total', 0));
});
test('admin users cannot create invalid users', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $testResponse = $this->actingAs($admin)->post('/app/admin/users', [
        'name' => '',
        'email' => 'invalid',
        'password' => 'weak',
        'role' => 'invalid-role',
    ]);

    $testResponse->assertSessionHasErrors(['name', 'email', 'password', 'role']);
});
test('admin users can update users', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $target = User::factory()->create(['role' => UserRole::User]);

    $testResponse = $this->actingAs($admin)->put('/app/admin/users/'.$target->id, [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'password' => '',
        'role' => UserRole::Admin->value,
    ]);

    $testResponse->assertRedirect('/app/admin/users');

    $this->assertDatabaseHas('users', [
        'id' => $target->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'role' => UserRole::Admin->value,
    ]);
});

test('admin user email updates reset verification status', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $target = User::factory()->create(['role' => UserRole::User]);
    expect($target->email_verified_at)->not->toBeNull();

    $this->actingAs($admin)->put('/app/admin/users/'.$target->id, [
        'name' => 'Verified User Updated',
        'email' => 'verified-updated@example.com',
        'password' => '',
        'role' => UserRole::User->value,
    ])->assertRedirect('/app/admin/users');

    expect($target->fresh()?->email_verified_at)->toBeNull();
});
test('admin users can delete other users', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $target = User::factory()->create(['role' => UserRole::User]);

    $testResponse = $this->actingAs($admin)->delete('/app/admin/users/'.$target->id);

    $testResponse->assertRedirect('/app/admin/users');
    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});
test('admin users cannot delete themselves', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $testResponse = $this->actingAs($admin)->delete('/app/admin/users/'.$admin->id);

    $testResponse->assertForbidden();
    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});
test('user listing query parameters are validated', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $testResponse = $this->actingAs($admin)->from('/app/admin/users')->get(
        '/app/admin/users?perPage=1000&page=0&sortBy=invalid&sortDirection=sideways&search='.str_repeat('x', 101)
    );

    $testResponse->assertRedirect('/app/admin/users');
    $testResponse->assertSessionHasErrors(['perPage', 'page', 'sortBy', 'sortDirection', 'search']);
});
test('user listing trims query input and applies default query values', function (): void {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'created_at' => now()->subHours(2),
    ]);
    User::factory()->create([
        'name' => 'Alice Trimmed',
        'email' => 'alice-trimmed@example.com',
        'role' => UserRole::User,
        'created_at' => now()->subMinute(),
    ]);
    User::factory()->create([
        'name' => 'Zulu Trimmed',
        'email' => 'zulu-trimmed@example.com',
        'role' => UserRole::User,
        'created_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get('/app/admin/users?search=%20%20Alice%20Trimmed%20%20')
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->where('pagination.current_page', 1)
            ->where('pagination.per_page', 10)
            ->has('items', 1)
            ->where('items.0.email', 'alice-trimmed@example.com')
        );
});
test('admin users can search users by name email and role', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    User::factory()->create([
        'name' => 'Alice Search',
        'email' => 'alice@example.com',
        'role' => UserRole::User,
    ]);
    User::factory()->create([
        'name' => 'Bob Search',
        'email' => 'bob@example.com',
        'role' => UserRole::Admin,
    ]);

    $this->actingAs($admin)
        ->get('/app/admin/users?search=alice')
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->has('items', 1)
            ->where('items.0.email', 'alice@example.com')
        );

    $this->actingAs($admin)
        ->get('/app/admin/users?search=bob@example.com')
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->has('items', 1)
            ->where('items.0.name', 'Bob Search')
        );

    $testResponse = $this->actingAs($admin)->get('/app/admin/users?search=admin');
    $testResponse->assertOk();
    $testResponse->assertSee('Bob Search');
    $testResponse->assertDontSee('Alice Search');
});
test('admin users can sort users by allowed fields', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    User::factory()->create(['name' => 'Alpha Sort', 'email' => 'alpha@example.com', 'role' => UserRole::User]);
    User::factory()->create(['name' => 'Zulu Sort', 'email' => 'zulu@example.com', 'role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->get('/app/admin/users?search=Sort&sortBy=name&sortDirection=asc')
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->where('items.0.name', 'Alpha Sort')
        );

    $this->actingAs($admin)
        ->get('/app/admin/users?search=Sort&sortBy=email&sortDirection=desc')
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->where('items.0.email', 'zulu@example.com')
        );
});
test('shared location prop preserves the query string', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // Server-driven listing pages rehydrate their table state from this prop.
    // Without the query string the page would fall back to the default sort
    // while the rendered rows stayed sorted the way the request asked for.
    $this->actingAs($admin)
        ->get('/app/admin/users?search=Sort&sortBy=name&sortDirection=asc&page=1')
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->where('location', function (string $location): bool {
                parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

                return parse_url($location, PHP_URL_PATH) === '/app/admin/users' && $query === [
                    'page' => '1',
                    'search' => 'Sort',
                    'sortBy' => 'name',
                    'sortDirection' => 'asc',
                ];
            })
        );
});
