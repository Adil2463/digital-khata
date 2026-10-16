<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('guests cannot access me endpoint', function (): void {
    $this->getJson('/api/v1/me')->assertUnauthorized();
});
test('authenticated users can access me endpoint', function (): void {
    $user = User::factory()->create(['role' => UserRole::User]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});
test('non admin users cannot access admin users api', function (): void {
    $user = User::factory()->create(['role' => UserRole::User]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/admin/users')->assertForbidden();
});

test('unverified users retain an explicit null verification date in api responses', function (): void {
    $user = User::factory()->unverified()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonStructure(['data' => ['email_verified_at']])
        ->assertJsonPath('data.email_verified_at', null);
});
test('admin users can manage users via api', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Sanctum::actingAs($admin);

    $this->getJson('/api/v1/admin/users')
        ->assertOk()
        ->assertJsonStructure(['data', 'links', 'meta']);

    $testResponse = $this->postJson('/api/v1/admin/users', [
        'name' => 'Api User',
        'email' => 'api-user@example.com',
        'password' => 'Password123!@#',
        'role' => UserRole::User->value,
    ]);

    $testResponse
        ->assertCreated()
        ->assertJsonPath('data.email', 'api-user@example.com');

    /** @var int $createdUserId */
    $createdUserId = $testResponse->json('data.id');

    $this->putJson('/api/v1/admin/users/'.$createdUserId, [
        'name' => 'Api User Updated',
        'email' => 'api-user-updated@example.com',
        'password' => '',
        'role' => UserRole::Admin->value,
    ])
        ->assertOk()
        ->assertJsonPath('data.email', 'api-user-updated@example.com');

    $this->deleteJson('/api/v1/admin/users/'.$createdUserId)->assertNoContent();
});

test('admin users cannot delete themselves via api', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Sanctum::actingAs($admin);

    $this->deleteJson('/api/v1/admin/users/'.$admin->id)->assertForbidden();
    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('admin user email updates reset verification status via api', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $target = User::factory()->create(['role' => UserRole::User]);
    expect($target->email_verified_at)->not->toBeNull();

    Sanctum::actingAs($admin);

    $this->putJson('/api/v1/admin/users/'.$target->id, [
        'name' => 'Verified User Updated',
        'email' => 'verified-updated@example.com',
        'password' => '',
        'role' => UserRole::User->value,
    ])
        ->assertOk()
        ->assertJsonPath('data.email', 'verified-updated@example.com')
        ->assertJsonPath('data.email_verified_at', null);

    expect($target->fresh()?->email_verified_at)->toBeNull();
});
test('admin user create validation errors are returned', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Sanctum::actingAs($admin);

    $this->postJson('/api/v1/admin/users', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => '123',
        'role' => 'invalid-role',
    ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
});
test('admin users can search and sort via api', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Sanctum::actingAs($admin);

    User::factory()->create([
        'name' => 'Api Alpha',
        'email' => 'api-alpha@example.com',
        'role' => UserRole::User,
    ]);
    User::factory()->create([
        'name' => 'Api Zulu',
        'email' => 'api-zulu@example.com',
        'role' => UserRole::Admin,
    ]);

    $this->getJson('/api/v1/admin/users?search=alpha')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.email', 'api-alpha@example.com');

    $this->getJson('/api/v1/admin/users?search=Api&sortBy=name&sortDirection=desc')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Api Zulu');
});
test('admin users api trims query input and applies default query values', function (): void {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'created_at' => now()->subHours(2),
    ]);
    Sanctum::actingAs($admin);

    User::factory()->create([
        'name' => 'Api Alice Trimmed',
        'email' => 'api-alice-trimmed@example.com',
        'role' => UserRole::User,
        'created_at' => now()->subMinute(),
    ]);
    User::factory()->create([
        'name' => 'Api Zulu Trimmed',
        'email' => 'api-zulu-trimmed@example.com',
        'role' => UserRole::User,
        'created_at' => now(),
    ]);

    $this->getJson('/api/v1/admin/users?search=%20%20Api%20Alice%20Trimmed%20%20')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.email', 'api-alice-trimmed@example.com')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 10);
});
test('admin users api query validation errors are returned', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Sanctum::actingAs($admin);

    $this->getJson('/api/v1/admin/users?perPage=1000&page=0&sortBy=invalid&sortDirection=sideways&search='.str_repeat('x', 101))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['perPage', 'page', 'sortBy', 'sortDirection', 'search']);
});
