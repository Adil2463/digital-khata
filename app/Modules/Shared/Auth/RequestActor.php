<?php

declare(strict_types=1);

namespace App\Modules\Shared\Auth;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

final class RequestActor
{
    /**
     * Resolve the authenticated application user from the request.
     *
     * @throws AuthenticationException
     */
    public static function from(Request $request): User
    {
        $actor = $request->user();

        if (! $actor instanceof User) {
            throw new AuthenticationException();
        }

        return $actor;
    }
}
