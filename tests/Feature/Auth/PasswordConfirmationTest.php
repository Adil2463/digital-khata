<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('confirm password screen can be rendered', function (): void {
    $user = User::factory()->create();

    $testResponse = $this->actingAs($user)->get('/auth/confirm-password');

    $testResponse->assertStatus(200);
});
test('password can be confirmed', function (): void {
    $user = User::factory()->create();

    $testResponse = $this->actingAs($user)->post('/auth/confirm-password', [
        'password' => 'password',
    ]);

    $testResponse->assertRedirect();
    $testResponse->assertSessionHasNoErrors();
});
test('password is not confirmed with invalid password', function (): void {
    $user = User::factory()->create();

    $testResponse = $this->actingAs($user)->post('/auth/confirm-password', [
        'password' => 'wrong-password',
    ]);

    $testResponse->assertSessionHasErrors();
});

test('password confirmation throttles repeated guesses and recovers after the window', function (): void {
    $this->actingAs(User::factory()->create());

    foreach (range(1, 5) as $attempt) {
        $this->post('/auth/confirm-password', ['password' => 'wrong-password'])->assertSessionHasErrors('password');
    }

    $this->post('/auth/confirm-password', ['password' => 'password'])
        ->assertTooManyRequests()
        ->assertSessionMissing('auth.password_confirmed_at');

    $this->travel(61)->seconds();
    $this->post('/auth/confirm-password', ['password' => 'password'])->assertRedirect();
});
