<?php

declare(strict_types=1);

namespace App\Modules\Shared\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * An expired session leaves nothing behind to tell a returning user apart from a new
 * visitor, so a long-lived marker cookie records that this browser signed in. Logging out
 * removes it; arriving at the login page with it still set means the session ended on its
 * own (idle timeout or revoked access) and earns an explanation.
 */
final class SessionEndNotice
{
    public const string COOKIE = 'khata_signed_in';

    public const string EXPIRED_MESSAGE = 'Your session has expired. Please log in again.';

    public const string LOGGED_OUT_MESSAGE = 'You have logged out.';

    private const int COOKIE_MINUTES = 60 * 24 * 30;

    public static function remember(): void
    {
        Cookie::queue(self::COOKIE, '1', self::COOKIE_MINUTES);
    }

    public static function forget(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE));
    }

    /**
     * Clears the marker and returns the expiry notice when the previous session ended
     * without a logout.
     */
    public static function consume(Request $request): ?string
    {
        if (! $request->hasCookie(self::COOKIE)) {
            return null;
        }

        self::forget();

        return self::EXPIRED_MESSAGE;
    }
}
