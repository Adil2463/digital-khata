<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Shared\Auth\SessionEndNotice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Symfony\Component\HttpFoundation\Cookie;

uses(RefreshDatabase::class);

test('login screen can be rendered', function (): void {
    $testResponse = $this->get('/auth/login');

    $testResponse
        ->assertStatus(200)
        ->assertViewHas('page.encryptHistory', true)
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->where('status', null)
            ->missing('canResetPassword')
        );
});
test('users can authenticate using the login screen', function (): void {
    $user = User::factory()->create();

    $testResponse = $this->post('/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $testResponse->assertRedirect(route('app.dashboard', absolute: false));
    $this->get('/app/dashboard')->assertViewHas('page.clearHistory', true);
    $this->get('/app/dashboard')->assertViewMissing('page.clearHistory');
});
test('authenticated users are redirected from guest screens to the dashboard', function (string $uri): void {
    $this->actingAs(User::factory()->create())
        ->get($uri)
        ->assertRedirect(route('app.dashboard', absolute: false));
})->with(['/auth/login']);
test('users can not authenticate with invalid password', function (): void {
    $user = User::factory()->create();

    $this->post('/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});
test('users can logout', function (): void {
    $user = User::factory()->create();

    $this->post('/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $testResponse = $this->post('/auth/logout');

    $this->assertGuest();
    $testResponse->assertRedirect('/auth/login');
    $this->get('/auth/login')
        ->assertViewHas('page.clearHistory', true)
        ->assertInertia(fn (Assert $assert): Assert => $assert->where('status', SessionEndNotice::LOGGED_OUT_MESSAGE));
    $this->get('/auth/login')->assertViewMissing('page.clearHistory');
});

test('signing in marks the browser and logging out clears the mark', function (): void {
    $user = User::factory()->create();

    $login = $this->post('/auth/login', ['email' => $user->email, 'password' => 'password']);
    $marker = collect($login->headers->getCookies())->first(fn (Cookie $cookie): bool => $cookie->getName() === SessionEndNotice::COOKIE);

    expect($marker)->toBeInstanceOf(Cookie::class)
        ->and($marker?->isHttpOnly())->toBeTrue();

    $logout = $this->post('/auth/logout');
    $cleared = collect($logout->headers->getCookies())->first(fn (Cookie $cookie): bool => $cookie->getName() === SessionEndNotice::COOKIE);

    expect($cleared?->isCleared())->toBeTrue();
});

test('a guest returning without logging out is told the session expired, once', function (): void {
    $response = $this->withCookie(SessionEndNotice::COOKIE, '1')->get('/auth/login');

    $response->assertInertia(fn (Assert $assert): Assert => $assert->where('status', SessionEndNotice::EXPIRED_MESSAGE));

    expect(collect($response->headers->getCookies())->first(fn (Cookie $cookie): bool => $cookie->getName() === SessionEndNotice::COOKIE)?->isCleared())->toBeTrue();
});

test('a first visit to the login page shows no session message', function (): void {
    $this->get('/auth/login')->assertInertia(fn (Assert $assert): Assert => $assert->where('status', null));
});
