<?php

declare(strict_types=1);

namespace App\Modules\Shared\Shop;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * The shop a request acts on always comes from the authenticated account, never from
 * request input, so a client cannot address another shop's data.
 */
final class CurrentShop
{
    /**
     * @throws AuthorizationException
     */
    public static function of(User $user): Shop
    {
        $shop = $user->shop;

        if (! $shop instanceof Shop) {
            throw new AuthorizationException('This account does not belong to a shop.');
        }

        return $shop;
    }
}
