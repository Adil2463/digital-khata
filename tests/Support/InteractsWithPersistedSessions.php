<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates through the real login form with a database session, then sends later
 * requests with the issued cookies. `actingAs()` would bypass the middleware under test.
 */
trait InteractsWithPersistedSessions
{
    protected function signInWithPersistedSession(User $user, string $password = 'password'): void
    {
        config()->set('session.driver', 'database');

        $response = $this->post('/auth/login', ['email' => $user->email, 'password' => $password]);
        $response->assertRedirect();

        $this->continueWithCookiesFrom($response);
    }

    /**
     * Sends the cookies a response issued with every later request, as a browser would.
     *
     * @param  TestResponse<Response>  $testResponse
     */
    protected function continueWithCookiesFrom(TestResponse $testResponse): void
    {
        $cookies = [];

        foreach ($testResponse->headers->getCookies() as $cookie) {
            $cookies[$cookie->getName()] = (string) $cookie->getValue();
        }

        $this->forgetRequestState();
        $this->withUnencryptedCookies($cookies);
    }

    /**
     * Makes the next request rebuild its guard and session from the persisted cookie.
     */
    protected function forgetRequestState(): void
    {
        Auth::forgetGuards();
        app('session')->forgetDrivers();
        app()->forgetInstance('session.store');
    }
}
