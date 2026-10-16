<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('shared guest props preserve explicit nulls and the complete request url', function (): void {
    $this->get('http://localhost/?campaign=starter')->assertInertia(fn (AssertableInertia $assertableInertia): AssertableInertia => $assertableInertia
        ->where('auth.user', null)
        ->where('flash', ['message' => null, 'error' => null, 'status' => null])
        ->where('location', 'http://localhost/?campaign=starter')
        ->where('sidebarOpen', true)
        ->where('appearance', 'light')
        ->has('quote.message')
        ->has('quote.author'));
});

test('shared auth uses the public dto and never exposes credentials', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get('/')->assertInertia(fn (AssertableInertia $assertableInertia): AssertableInertia => $assertableInertia
        ->where('auth.user.id', $user->id)
        ->where('auth.user.email_verified_at', null)
        ->missing('auth.user.password')
        ->missing('auth.user.remember_token'));
});

test('shared flash accepts text and normalizes incompatible session values', function (): void {
    $this->withSession(['message' => 'Saved', 'error' => ['unexpected'], 'status' => 12])
        ->get('/')->assertInertia(fn (AssertableInertia $assertableInertia): AssertableInertia => $assertableInertia
        ->where('flash', ['message' => 'Saved', 'error' => null, 'status' => null]));
});
