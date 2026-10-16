<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAccountCanSignIn
{
    public const string DEACTIVATED_MESSAGE = 'Your account is deactivated. Ask the shop owner to reactivate it.';

    /**
     * Checked on every request so a deactivation takes effect immediately, whatever the
     * session driver and however much of the session lifetime remains.
     *
     * @param  Closure(Request): Response  $next
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next, string $guard = 'web'): Response
    {
        $user = Auth::guard($guard)->user();

        if (! $user instanceof User || $user->canSignIn()) {
            return $next($request);
        }

        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->flash('status', self::DEACTIVATED_MESSAGE);
        }

        throw new AuthenticationException(self::DEACTIVATED_MESSAGE, [$guard]);
    }
}
