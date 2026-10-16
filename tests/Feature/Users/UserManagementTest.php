<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

const STAFF_PASSWORD = 'Counter-Staff-2026';

/**
 * @param  array<string, string>  $overrides
 * @return array<string, string>
 */
function staffPayload(array $overrides = []): array
{
    return [
        'name' => 'Ali Raza',
        'email' => 'ali@shop.local',
        'password' => STAFF_PASSWORD,
        'password_confirmation' => STAFF_PASSWORD,
        'role' => UserRole::User->value,
        ...$overrides,
    ];
}

function insertDatabaseSession(User $user, string $id): void
{
    DB::table('sessions')->insert(['id' => $id, 'user_id' => $user->id, 'payload' => '', 'last_activity' => now()->getTimestamp()]);
}

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

test('staff cannot reach or change account management', function (string $method, string $uri): void {
    $staff = User::factory()->create();
    $colleague = User::factory()->create(['name' => 'Colleague']);

    $this->actingAs($staff)
        ->call($method, str_replace('{user}', (string) $colleague->id, $uri), staffPayload(['email' => 'new@shop.local']))
        ->assertForbidden();

    expect(User::query()->where('email', 'new@shop.local')->exists())->toBeFalse()
        ->and($colleague->fresh()?->name)->toBe('Colleague')
        ->and($colleague->fresh()?->deactivated_at)->toBeNull();
})->with([
    'list' => ['GET', '/app/admin/users'],
    'create' => ['POST', '/app/admin/users'],
    'update' => ['PUT', '/app/admin/users/{user}'],
    'deactivate' => ['PATCH', '/app/admin/users/{user}/deactivate'],
    'reactivate' => ['PATCH', '/app/admin/users/{user}/reactivate'],
]);

test('owners see only accounts of their own shop', function (): void {
    $owner = User::factory()->owner()->create(['name' => 'Own Owner']);
    User::factory()->deactivated()->create(['name' => 'Former Helper']);
    User::factory()->create(['name' => 'Foreign Staff', 'shop_id' => Shop::factory()->create()->id]);

    $this->actingAs($owner)
        ->get('/app/admin/users?sortBy=name&sortDirection=asc')
        ->assertOk()
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->component('modules/users/pages/Index')
            ->has('items', 2)
            ->where('items.0.name', 'Former Helper')
            ->whereNot('items.0.deactivated_at', null)
            ->where('items.1.name', 'Own Owner')
            ->where('items.1.deactivated_at', null)
            ->missing('items.0.password'));
});

test('owners create verified staff inside their own shop', function (): void {
    $owner = User::factory()->owner()->create();
    $foreignShop = Shop::factory()->create();

    $this->actingAs($owner)
        ->post('/app/admin/users', staffPayload(['shop_id' => (string) $foreignShop->id, 'email' => '  Ali@Shop.Local ']))
        ->assertSessionHasNoErrors()
        ->assertRedirect('/app/admin/users')
        ->assertSessionHas('message', 'Ali Raza can now log in as ali@shop.local.');

    $user = User::query()->where('email', 'ali@shop.local')->sole();

    expect($user->shop_id)->toBe($owner->shop_id)
        ->and($user->role)->toBe(UserRole::User)
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->deactivated_at)->toBeNull()
        ->and($user->password)->not->toBe(STAFF_PASSWORD)
        ->and(Hash::check(STAFF_PASSWORD, $user->password))->toBeTrue();
});

test('newly created staff log in without an email verification screen', function (): void {
    $owner = User::factory()->owner()->create();
    $this->actingAs($owner)->post('/app/admin/users', staffPayload())->assertRedirect('/app/admin/users');
    $this->post('/auth/logout');

    $this->post('/auth/login', ['email' => 'ali@shop.local', 'password' => STAFF_PASSWORD])
        ->assertRedirect(route('app.dashboard', absolute: false));

    $this->get('/app/dashboard')->assertOk();
});

test('duplicate logins are rejected regardless of case', function (): void {
    $owner = User::factory()->owner()->create();
    User::factory()->create(['email' => 'ali@shop.local']);

    $this->actingAs($owner)
        ->post('/app/admin/users', staffPayload(['email' => 'ALI@shop.local']))
        ->assertSessionHasErrors(['email' => 'The login has already been taken.']);

    expect(User::query()->where('email', 'ali@shop.local')->count())->toBe(1);
});

test('weak or unconfirmed passwords are rejected', function (array $overrides, string $message): void {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)
        ->post('/app/admin/users', staffPayload($overrides))
        ->assertSessionHasErrors(['password' => $message]);

    expect(User::query()->where('email', 'ali@shop.local')->exists())->toBeFalse();
})->with([
    'too short' => [['password' => 'abc12345', 'password_confirmation' => 'abc12345'], 'The password field must be at least 10 characters.'],
    'no numbers' => [['password' => 'only-letters-here', 'password_confirmation' => 'only-letters-here'], 'The password field must contain at least one number.'],
    'not confirmed' => [['password_confirmation' => 'Something-Else-1'], 'The password field confirmation does not match.'],
    'missing' => [['password' => '', 'password_confirmation' => ''], 'The password field is required.'],
]);

test('owners cannot create invalid accounts', function (): void {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)
        ->post('/app/admin/users', ['name' => '', 'email' => 'invalid', 'password' => 'weak', 'role' => 'invalid-role'])
        ->assertSessionHasErrors(['name', 'email', 'password', 'role']);
});

test('owners edit staff name, login and role without breaking verification', function (): void {
    $owner = User::factory()->owner()->create();
    $staff = User::factory()->unverified()->create();

    $this->actingAs($owner)
        ->put('/app/admin/users/'.$staff->id, ['name' => 'Updated Name', 'email' => 'updated@shop.local', 'password' => '', 'role' => UserRole::Admin->value])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/app/admin/users');

    $staff->refresh();

    expect($staff->only(['name', 'email']))->toBe(['name' => 'Updated Name', 'email' => 'updated@shop.local'])
        ->and($staff->role)->toBe(UserRole::Admin)
        ->and($staff->hasVerifiedEmail())->toBeTrue();
});

test('changing a staff login revokes their sessions and tokens', function (): void {
    config(['session.driver' => 'database']);
    $owner = User::factory()->owner()->create();
    $staff = User::factory()->create();
    $staff->createToken('phone');
    insertDatabaseSession($staff, 'staff-device');

    $this->actingAs($owner)
        ->put('/app/admin/users/'.$staff->id, ['name' => $staff->name, 'email' => 'renamed@shop.local', 'role' => UserRole::User->value])
        ->assertRedirect('/app/admin/users');

    expect(DB::table('sessions')->where('user_id', $staff->id)->exists())->toBeFalse()
        ->and($staff->tokens()->count())->toBe(0);
});

test('owners reset a staff password and old credentials stop working', function (): void {
    config(['session.driver' => 'database']);
    $owner = User::factory()->owner()->create();
    $staff = User::factory()->create(['email' => 'helper@shop.local']);
    insertDatabaseSession($staff, 'staff-device');
    $rememberToken = $staff->remember_token;

    $this->actingAs($owner)
        ->put('/app/admin/users/'.$staff->id, [
            'name' => $staff->name,
            'email' => $staff->email,
            'role' => UserRole::User->value,
            'password' => 'Fresh-Staff-Pass-7',
            'password_confirmation' => 'Fresh-Staff-Pass-7',
        ])
        ->assertSessionHasNoErrors();

    $staff->refresh();

    expect(Hash::check('Fresh-Staff-Pass-7', $staff->password))->toBeTrue()
        ->and(Hash::check('password', $staff->password))->toBeFalse()
        ->and($staff->remember_token)->not->toBe($rememberToken)
        ->and(DB::table('sessions')->where('user_id', $staff->id)->exists())->toBeFalse();
});

test('owners editing their own login stay signed in on this device', function (): void {
    config(['session.driver' => 'database']);
    $owner = User::factory()->owner()->create();
    insertDatabaseSession($owner, 'owner-phone');

    $this->actingAs($owner)
        ->put('/app/admin/users/'.$owner->id, ['name' => $owner->name, 'email' => 'boss@shop.local', 'role' => UserRole::Admin->value])
        ->assertSessionHasNoErrors();

    expect(DB::table('sessions')->where('id', 'owner-phone')->exists())->toBeFalse();
    $this->get('/app/admin/users')->assertOk();
});

test('owners cannot remove their own owner role', function (): void {
    $owner = User::factory()->owner()->create();
    User::factory()->owner()->create();

    $this->actingAs($owner)
        ->put('/app/admin/users/'.$owner->id, ['name' => $owner->name, 'email' => $owner->email, 'role' => UserRole::User->value])
        ->assertSessionHasErrors(['role' => 'You cannot remove your own Owner role.']);

    expect($owner->fresh()?->role)->toBe(UserRole::Admin);
});

test('owners deactivate staff without deleting them and revoke all access', function (): void {
    config(['session.driver' => 'database']);
    $owner = User::factory()->owner()->create();
    $staff = User::factory()->create(['name' => 'Ali Raza']);
    $staff->createToken('phone');
    insertDatabaseSession($staff, 'staff-device');

    $this->actingAs($owner)
        ->patch('/app/admin/users/'.$staff->id.'/deactivate')
        ->assertRedirect('/app/admin/users')
        ->assertSessionHas('message', 'Ali Raza was deactivated and signed out everywhere.');

    $staff->refresh();

    expect($staff->deactivated_at)->not->toBeNull()
        ->and($staff->tokens()->count())->toBe(0)
        ->and(DB::table('sessions')->where('user_id', $staff->id)->exists())->toBeFalse();
    $this->assertDatabaseHas('users', ['id' => $staff->id, 'email' => $staff->email]);
});

test('deactivated staff cannot log in until reactivated', function (): void {
    $owner = User::factory()->owner()->create();
    $staff = User::factory()->create(['email' => 'helper@shop.local']);

    $this->actingAs($owner)->patch('/app/admin/users/'.$staff->id.'/deactivate')->assertRedirect();
    $this->post('/auth/logout');

    $this->post('/auth/login', ['email' => 'helper@shop.local', 'password' => 'password'])
        ->assertSessionHasErrors(['email' => 'Your account is deactivated. Ask the shop owner to reactivate it.']);
    $this->assertGuest();

    $this->actingAs($owner)->patch('/app/admin/users/'.$staff->id.'/reactivate')
        ->assertRedirect('/app/admin/users')
        ->assertSessionHas('message', $staff->name.' can log in again.');
    $this->post('/auth/logout');

    $this->post('/auth/login', ['email' => 'helper@shop.local', 'password' => 'password'])
        ->assertRedirect(route('app.dashboard', absolute: false));
    $this->assertAuthenticatedAs($staff);
});

test('owners cannot deactivate themselves', function (): void {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)
        ->patch('/app/admin/users/'.$owner->id.'/deactivate')
        ->assertForbidden();

    expect($owner->fresh()?->deactivated_at)->toBeNull();
});

test('status changes must match the current status', function (): void {
    $owner = User::factory()->owner()->create();
    $active = User::factory()->create();
    $deactivated = User::factory()->deactivated()->create();

    $this->actingAs($owner)->patch('/app/admin/users/'.$active->id.'/reactivate')->assertForbidden();
    $this->actingAs($owner)->patch('/app/admin/users/'.$deactivated->id.'/deactivate')->assertForbidden();
});

test('owners cannot touch accounts of another shop', function (string $method, string $suffix): void {
    $owner = User::factory()->owner()->create();
    $foreign = User::factory()->create(['shop_id' => Shop::factory()->create()->id, 'name' => 'Foreign']);

    $this->actingAs($owner)
        ->call($method, '/app/admin/users/'.$foreign->id.$suffix, staffPayload(['email' => $foreign->email]))
        ->assertNotFound();

    expect($foreign->fresh()?->name)->toBe('Foreign')
        ->and($foreign->fresh()?->deactivated_at)->toBeNull();
})->with([
    'update' => ['PUT', ''],
    'deactivate' => ['PATCH', '/deactivate'],
]);

test('accounts can no longer be deleted', function (): void {
    $owner = User::factory()->owner()->create();
    $staff = User::factory()->create();

    $this->actingAs($owner)->delete('/app/admin/users/'.$staff->id)->assertMethodNotAllowed();
    $this->assertDatabaseHas('users', ['id' => $staff->id]);
});

test('empty user pages still carry the shared pagination envelope', function (): void {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)->get('/app/admin/users?search=nonexistent-user')
        ->assertOk()
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->has('items', 0)
            ->where('pagination.current_page', 1)
            ->where('pagination.last_page', 1)
            ->where('pagination.total', 0));
});

test('user listing query parameters are validated', function (): void {
    $owner = User::factory()->owner()->create();

    $testResponse = $this->actingAs($owner)->from('/app/admin/users')->get(
        '/app/admin/users?perPage=1000&page=0&sortBy=invalid&sortDirection=sideways&search='.str_repeat('x', 101)
    );

    $testResponse->assertRedirect('/app/admin/users');
    $testResponse->assertSessionHasErrors(['perPage', 'page', 'sortBy', 'sortDirection', 'search']);
});

test('user listing trims query input and applies default query values', function (): void {
    $owner = User::factory()->owner()->create(['created_at' => now()->subHours(2)]);
    User::factory()->create(['name' => 'Alice Trimmed', 'email' => 'alice-trimmed@example.com', 'created_at' => now()->subMinute()]);
    User::factory()->create(['name' => 'Zulu Trimmed', 'email' => 'zulu-trimmed@example.com', 'created_at' => now()]);

    $this->actingAs($owner)
        ->get('/app/admin/users?search=%20%20Alice%20Trimmed%20%20')
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->where('pagination.current_page', 1)
            ->where('pagination.per_page', 10)
            ->has('items', 1)
            ->where('items.0.email', 'alice-trimmed@example.com')
        );
});

test('owners can search accounts by name, login and role label', function (): void {
    $owner = User::factory()->owner()->create(['name' => 'Shop Owner', 'email' => 'owner@shop.local']);
    User::factory()->create(['name' => 'Alice Search', 'email' => 'alice@example.com']);

    $this->actingAs($owner)
        ->get('/app/admin/users?search=alice')
        ->assertInertia(fn (Assert $assert): Assert => $assert->has('items', 1)->where('items.0.email', 'alice@example.com'));

    $this->actingAs($owner)
        ->get('/app/admin/users?search=owner@shop.local')
        ->assertInertia(fn (Assert $assert): Assert => $assert->has('items', 1)->where('items.0.name', 'Shop Owner'));

    $this->actingAs($owner)
        ->get('/app/admin/users?search=staff')
        ->assertInertia(fn (Assert $assert): Assert => $assert->has('items', 1)->where('items.0.name', 'Alice Search'));
});

test('owners can sort accounts by allowed fields', function (): void {
    $owner = User::factory()->owner()->create();
    User::factory()->create(['name' => 'Alpha Sort', 'email' => 'alpha@example.com']);
    User::factory()->create(['name' => 'Zulu Sort', 'email' => 'zulu@example.com']);

    $this->actingAs($owner)
        ->get('/app/admin/users?search=Sort&sortBy=name&sortDirection=asc')
        ->assertInertia(fn (Assert $assert): Assert => $assert->where('items.0.name', 'Alpha Sort'));

    $this->actingAs($owner)
        ->get('/app/admin/users?search=Sort&sortBy=email&sortDirection=desc')
        ->assertInertia(fn (Assert $assert): Assert => $assert->where('items.0.email', 'zulu@example.com'));
});

test('shared location prop preserves the query string', function (): void {
    $owner = User::factory()->owner()->create();

    // Server-driven listing pages rehydrate their table state from this prop.
    // Without the query string the page would fall back to the default sort
    // while the rendered rows stayed sorted the way the request asked for.
    $this->actingAs($owner)
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
