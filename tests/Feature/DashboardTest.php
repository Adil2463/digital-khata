<?php

declare(strict_types=1);

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function (): void {
    $testResponse = $this->get('/app/dashboard');
    $testResponse->assertRedirect('/auth/login');
});
test('authenticated users can visit the dashboard', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $testResponse = $this->get('/app/dashboard');
    $testResponse->assertStatus(200);
    $testResponse->assertInertia(fn (Assert $assert): Assert => $assert->component('modules/dashboard/pages/Index'));
});

test('owners and staff both reach the dashboard with the shop clock', function (string $role): void {
    $this->travelTo(CarbonImmutable::parse('2026-10-16 19:00:00', 'UTC'));
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->get('/app/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->component('modules/dashboard/pages/Index')
            ->where('shopTime', '2026-10-16T19:00:00+00:00')
            ->where('shop.timezone', 'Asia/Karachi'));
})->with(['owner' => 'admin', 'staff' => 'user']);
