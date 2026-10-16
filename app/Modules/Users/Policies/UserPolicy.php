<?php

declare(strict_types=1);

namespace App\Modules\Users\Policies;

use App\Enums\UserRole;
use App\Models\User;

final class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, User $model): bool
    {
        return $this->isAdmin($user) && $model->exists;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, User $model): bool
    {
        return $this->isAdmin($user) && $model->exists;
    }

    public function delete(User $user, User $model): bool
    {
        return $this->isAdmin($user) && $user->id !== $model->id;
    }

    private function isAdmin(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
