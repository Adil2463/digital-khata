<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests\Concerns;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

trait AuthorizesRoutedAccount
{
    /**
     * Returns the policy response so deny messages and 404s for other shops reach the client.
     */
    protected function authorizeRoutedAccount(string $ability): Response
    {
        $actor = $this->user();
        $account = $this->route('user');

        if (! $actor instanceof User || ! $account instanceof User) {
            return Response::deny();
        }

        return Gate::forUser($actor)->inspect($ability, $account);
    }
}
