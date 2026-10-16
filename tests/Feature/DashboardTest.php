<?php

declare(strict_types=1);

use App\Models\User;
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
