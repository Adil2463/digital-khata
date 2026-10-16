<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureAccountCanSignIn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('guests at the root land on the login page', function (): void {
    $this->get('/')->assertRedirect('/auth/login');

    $this->followingRedirects()
        ->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $assert): Assert => $assert->component('modules/auth/pages/Login'));
});

test('signed-in owners and staff at the root land on the dashboard', function (User $user): void {
    $this->actingAs($user)->get('/')->assertRedirect('/app/dashboard');

    $this->actingAs($user)
        ->followingRedirects()
        ->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $assert): Assert => $assert->component('modules/dashboard/pages/Index'));
})->with([
    'owner' => fn (): User => User::factory()->owner()->create(),
    'staff' => fn (): User => User::factory()->create(),
]);

test('accounts that may not sign in are signed out at the root instead of redirected inside', function (User $user, ?string $status): void {
    $this->actingAs($user)
        ->get('/')
        ->assertRedirect('/auth/login')
        ->assertSessionHas('status', $status);

    $this->assertGuest();
})->with([
    'deactivated account' => [fn (): User => User::factory()->deactivated()->create(), EnsureAccountCanSignIn::DEACTIVATED_MESSAGE],
    'account without a shop' => [fn (): User => User::factory()->withoutShop()->create(), EnsureAccountCanSignIn::DEACTIVATED_MESSAGE],
]);

test('the root keeps its public route name for generated public pages', function (): void {
    expect(route('marketing.home', absolute: false))->toBe('/');
});
