<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('auth-sensitive', fn (Request $request): Limit => Limit::perMinute(5)->by((string) $request->ip()));
        // `LoginRequest` already limits 5 attempts per login and IP; this caps guessing across many logins.
        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(20)->by((string) $request->ip()));

        // Offline installation: no breach-list lookup (`uncompromised()` needs the internet).
        Password::defaults(static fn (): Password => Password::min(10)->letters()->numbers());

        VerifyEmail::createUrlUsing(fn (User $user): string => URL::temporarySignedRoute(
            'auth.verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ],
        ));
    }
}
