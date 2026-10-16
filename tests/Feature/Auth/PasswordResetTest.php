<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('reset password link screen can be rendered', function (): void {
    $testResponse = $this->get('/auth/forgot-password');

    $testResponse
        ->assertStatus(200)
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->where('status', null)
        );
});
test('reset password link can be requested', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/auth/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});
test('reset password screen can be rendered', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/auth/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $resetPassword): true {
        $testResponse = $this->get('/auth/reset-password/'.$resetPassword->token);

        $testResponse
            ->assertStatus(200)
            ->assertInertia(fn (Assert $assert): Assert => $assert
                ->where('email', '')
                ->where('token', $resetPassword->token)
            );

        return true;
    });
});
test('password can be reset with valid token', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/auth/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $resetPassword) use ($user): true {
        $testResponse = $this->post('/auth/reset-password', [
            'token' => $resetPassword->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $testResponse
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('auth.login.create', absolute: false));

        return true;
    });
});
