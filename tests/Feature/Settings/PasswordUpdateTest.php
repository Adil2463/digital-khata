<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('password can be updated', function (): void {
    $user = User::factory()->create();

    $testResponse = $this
        ->actingAs($user)
        ->from('/app/settings/password')
        ->put('/app/settings/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $testResponse
        ->assertSessionHasNoErrors()
        ->assertRedirect('/app/settings/password');

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});
test('correct password must be provided to update password', function (): void {
    $user = User::factory()->create();

    $testResponse = $this
        ->actingAs($user)
        ->from('/app/settings/password')
        ->put('/app/settings/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $testResponse
        ->assertSessionHasErrors('current_password')
        ->assertRedirect('/app/settings/password');
});
