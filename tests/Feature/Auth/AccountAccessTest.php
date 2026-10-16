<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureAccountCanSignIn;
use App\Models\User;
use App\Modules\Shared\Auth\CredentialRotator;
use App\Modules\Shared\Auth\DemoAccounts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Symfony\Component\HttpFoundation\Cookie;

uses(RefreshDatabase::class);

test('deactivated accounts are refused at login with a clear message', function (): void {
    $user = User::factory()->deactivated()->create(['email' => 'helper@shop.local']);

    $this->post('/auth/login', ['email' => 'helper@shop.local', 'password' => 'password'])
        ->assertSessionHasErrors(['email' => EnsureAccountCanSignIn::DEACTIVATED_MESSAGE]);

    $this->assertGuest();
    expect($user->fresh()?->deactivated_at)->not->toBeNull();
});

test('a wrong password never reveals that an account is deactivated', function (): void {
    User::factory()->deactivated()->create(['email' => 'helper@shop.local']);

    $this->post('/auth/login', ['email' => 'helper@shop.local', 'password' => 'wrong-password'])
        ->assertSessionHasErrors(['email' => trans('auth.failed')]);
});

test('accounts that belong to no shop cannot log in', function (): void {
    User::factory()->withoutShop()->create(['email' => 'orphan@shop.local']);

    $this->post('/auth/login', ['email' => 'orphan@shop.local', 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('logins are matched case-insensitively', function (): void {
    $user = User::factory()->create(['email' => 'helper@shop.local']);

    $this->post('/auth/login', ['email' => 'Helper@Shop.Local', 'password' => 'password'])
        ->assertRedirect(route('app.dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('an open session ends on its next request after deactivation, even if its session row survives', function (): void {
    $staff = User::factory()->create();
    $this->signInWithPersistedSession($staff);
    $this->get('/app/dashboard')->assertOk();

    // Bypass credential revocation on purpose: enforcement must not depend on deleting sessions.
    DB::table('users')->where('id', $staff->id)->update(['deactivated_at' => now()]);
    $this->forgetRequestState();

    $this->continueWithCookiesFrom($this->get('/app/dashboard')->assertRedirect('/auth/login'));

    $this->get('/auth/login')
        ->assertOk()
        ->assertInertia(fn (Assert $assert): Assert => $assert->where('status', EnsureAccountCanSignIn::DEACTIVATED_MESSAGE));
    expect(Auth::guard('web')->check())->toBeFalse();
});

test('deactivation through the owner screen ends the staff session immediately', function (): void {
    $owner = User::factory()->owner()->create();
    $staff = User::factory()->create();
    $this->signInWithPersistedSession($staff);

    app(CredentialRotator::class)->revokeAccess($staff);
    $staff->forceFill(['deactivated_at' => now()])->save();
    expect(DB::table('sessions')->where('user_id', $staff->id)->exists())->toBeFalse();
    $this->forgetRequestState();

    $this->get('/app/dashboard')->assertRedirect('/auth/login');
    expect($owner->fresh()?->canSignIn())->toBeTrue();
});

test('reactivated staff must log in again but can do so', function (): void {
    $staff = User::factory()->deactivated()->create(['email' => 'helper@shop.local']);
    $staff->forceFill(['deactivated_at' => null])->save();

    $this->post('/auth/login', ['email' => 'helper@shop.local', 'password' => 'password'])
        ->assertRedirect(route('app.dashboard', absolute: false));
    $this->get('/app/dashboard')->assertOk();
});

test('sessions expire after the configured idle time', function (): void {
    expect(config('session.lifetime'))->toBe(30);

    $this->signInWithPersistedSession(User::factory()->create());

    $this->travel(29)->minutes();
    $this->forgetRequestState();
    $this->get('/app/dashboard')->assertOk();

    // Each request renews the idle window; 31 quiet minutes then end the session.
    $this->travel(31)->minutes();
    $this->forgetRequestState();
    $this->get('/app/dashboard')->assertRedirect('/auth/login');
});

test('remember me is not offered and never issues a long-lived cookie', function (): void {
    $user = User::factory()->create();

    $response = $this->post('/auth/login', ['email' => $user->email, 'password' => 'password', 'remember' => '1']);

    $response->assertRedirect(route('app.dashboard', absolute: false));

    $cookieNames = array_map(static fn (Cookie $cookie): string => $cookie->getName(), $response->headers->getCookies());
    expect(array_filter($cookieNames, static fn (string $name): bool => str_starts_with($name, 'remember_web_')))->toBe([]);
});

test('repeated failures lock out a login even with the right password', function (): void {
    $user = User::factory()->create(['email' => 'helper@shop.local']);

    foreach (range(1, 5) as $attempt) {
        $this->post('/auth/login', ['email' => 'helper@shop.local', 'password' => 'wrong-'.$attempt]);
    }

    $this->post('/auth/login', ['email' => 'helper@shop.local', 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    expect($user->fresh())->not->toBeNull();
});

test('one address cannot spray guesses across many logins', function (): void {
    foreach (range(1, 20) as $attempt) {
        $this->post('/auth/login', ['email' => sprintf('guess%d@shop.local', $attempt), 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');
    }

    $this->post('/auth/login', ['email' => 'guess21@shop.local', 'password' => 'wrong-password'])
        ->assertTooManyRequests();
});

test('an expired page sends guests to login and keeps signed-in users on the form', function (): void {
    Route::middleware('web')->post('/__test/expired-form', static fn (): never => throw new TokenMismatchException('CSRF token mismatch.'));

    $this->post('/__test/expired-form')
        ->assertRedirect(route('auth.login.create'))
        ->assertSessionHas('status', 'Your session expired after a period of inactivity. Please log in again.');

    $this->actingAs(User::factory()->create())
        ->from('/app/settings/profile')
        ->post('/__test/expired-form')
        ->assertRedirect('/app/settings/profile')
        ->assertSessionHas('error', 'This page expired. Please try again.');

    $this->postJson('/__test/expired-form')->assertStatus(419);
});

test('locked demo accounts stay locked', function (): void {
    $demoAccounts = array_map(
        static fn (array $credential): User => User::factory()->create([
            'email' => $credential['email'],
            'role' => $credential['role'],
            'password' => $credential['password'],
        ]),
        DemoAccounts::CREDENTIALS,
    );
    array_map(app(CredentialRotator::class)->lock(...), $demoAccounts);

    expect(DemoAccounts::usersWithPublishedPasswords())->toBe([]);

    foreach (DemoAccounts::CREDENTIALS as $credential) {
        $this->post('/auth/login', ['email' => $credential['email'], 'password' => $credential['password']])
            ->assertSessionHasErrors(['email' => trans('auth.failed')]);
        $this->assertGuest();
    }
});
