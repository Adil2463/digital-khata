<?php

declare(strict_types=1);

use App\Enums\ShopTimezone;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('shared guest props preserve explicit nulls and the complete request url', function (): void {
    $this->get('http://localhost/auth/login?campaign=starter')->assertInertia(fn (AssertableInertia $assertableInertia): AssertableInertia => $assertableInertia
        ->where('auth.user', null)
        ->where('shop', null)
        ->where('flash', ['message' => null, 'error' => null, 'status' => null])
        ->where('location', 'http://localhost/auth/login?campaign=starter')
        ->where('sidebarOpen', true)
        ->where('appearance', 'light')
        ->has('quote.message')
        ->has('quote.author'));
});

test('shared auth uses the public dto and never exposes credentials', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get('/app/settings/profile')->assertInertia(fn (AssertableInertia $assertableInertia): AssertableInertia => $assertableInertia
        ->where('auth.user.id', $user->id)
        ->where('auth.user.email_verified_at', null)
        ->missing('auth.user.password')
        ->missing('auth.user.remember_token'));
});

test('signed-in owners and staff share only their own shop name and timezone', function (string $role): void {
    Shop::factory()->create(['name' => 'Other Shop']);
    $shop = Shop::factory()->create(['name' => 'Bismillah Karyana Store', 'phone' => '0300-1234567', 'address' => 'Main Bazaar']);
    $user = User::factory()->create(['shop_id' => $shop->id, 'role' => $role]);

    $this->actingAs($user)->get('/app/dashboard')->assertInertia(fn (AssertableInertia $assertableInertia): AssertableInertia => $assertableInertia
        ->where('shop', ['name' => 'Bismillah Karyana Store', 'timezone' => ShopTimezone::AsiaKarachi->value]));
})->with(['owner' => 'admin', 'staff' => 'user']);

test('shared flash accepts text and normalizes incompatible session values', function (): void {
    $this->withSession(['message' => 'Saved', 'error' => ['unexpected'], 'status' => 12])
        ->get('/auth/login')->assertInertia(fn (AssertableInertia $assertableInertia): AssertableInertia => $assertableInertia
        ->where('flash', ['message' => 'Saved', 'error' => null, 'status' => null]));
});
