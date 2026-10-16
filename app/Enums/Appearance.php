<?php

declare(strict_types=1);

namespace App\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * The visitor's color scheme.
 *
 * Persisted in an unencrypted cookie (see `bootstrap/app.php`) so the server can
 * render `class="dark"` on the root element itself. That is what makes the theme
 * correct on the very first paint, with no boot script to run and nothing left for
 * the client to re-decide after hydration.
 *
 * Exported to TypeScript so the frontend spends the same two values the backend
 * normalizes to, instead of a hand-written union that can drift.
 */
#[TypeScript]
enum Appearance: string
{
    case Light = 'light';
    case Dark = 'dark';

    public const COOKIE = 'appearance';

    /**
     * Cookies are visitor-controlled, so anything unrecognised falls back to light.
     */
    public static function fromCookie(mixed $value): self
    {
        return is_string($value) ? self::tryFrom($value) ?? self::Light : self::Light;
    }
}
