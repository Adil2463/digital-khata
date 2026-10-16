<?php

declare(strict_types=1);

namespace App\Modules\Users\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Owners manage the accounts of their own shop. Accounts are deactivated, never deleted.
 * Business invariants such as "keep one active owner" live in `UserCommands`.
 */
final class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->managesAccounts($user);
    }

    public function view(User $user, User $model): Response
    {
        return $this->managesAccountOf($user, $model);
    }

    public function create(User $user): bool
    {
        return $this->managesAccounts($user);
    }

    public function update(User $user, User $model): Response
    {
        return $this->managesAccountOf($user, $model);
    }

    public function deactivate(User $user, User $model): Response
    {
        $response = $this->managesAccountOf($user, $model);

        if ($response->denied()) {
            return $response;
        }

        if ($user->is($model)) {
            return Response::deny('You cannot deactivate your own account.');
        }

        return $model->deactivated_at === null
            ? Response::allow()
            : Response::deny('This account is already deactivated.');
    }

    public function reactivate(User $user, User $model): Response
    {
        $response = $this->managesAccountOf($user, $model);

        if ($response->denied()) {
            return $response;
        }

        return $model->deactivated_at !== null
            ? Response::allow()
            : Response::deny('This account is already active.');
    }

    private function managesAccounts(User $user): bool
    {
        return $user->isOwner() && $user->canSignIn();
    }

    private function managesAccountOf(User $user, User $model): Response
    {
        if (! $this->managesAccounts($user)) {
            return Response::deny();
        }

        return $model->belongsToShopOf($user) ? Response::allow() : Response::denyAsNotFound();
    }
}
