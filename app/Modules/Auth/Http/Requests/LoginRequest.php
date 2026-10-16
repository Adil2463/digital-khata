<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use App\Http\Middleware\EnsureAccountCanSignIn;
use App\Models\User;
use App\Modules\Auth\Data\LoginData;
use App\Modules\Shared\Http\Requests\DataFormRequest;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @extends DataFormRequest<LoginData>
 */
final class LoginRequest extends DataFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginData = $this->toDto();
        $passwordMatchedBlockedAccount = false;

        // The callback only runs after the password is verified, so the specific message
        // below never tells a guesser that an account exists.
        $authenticated = Auth::attemptWhen(
            [
                'email' => Str::lower($loginData->email),
                'password' => $loginData->password,
            ],
            static function (User $user) use (&$passwordMatchedBlockedAccount): bool {
                $passwordMatchedBlockedAccount = ! $user->canSignIn();

                return ! $passwordMatchedBlockedAccount;
            },
        );

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => $passwordMatchedBlockedAccount ? EnsureAccountCanSignIn::DEACTIVATED_MESSAGE : trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')->toString()).'|'.$this->ip());
    }

    protected function dataClass(): string
    {
        return LoginData::class;
    }
}
