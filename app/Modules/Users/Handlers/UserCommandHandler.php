<?php

declare(strict_types=1);

namespace App\Modules\Users\Handlers;

use App\Models\User;
use App\Modules\Users\Commands\UserCommandResult;
use App\Modules\Users\Commands\UserCommands;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Events\UserManagementEvent;
use App\Modules\Users\Support\UserActionContext;
use Illuminate\Support\Facades\Event;

final readonly class UserCommandHandler
{
    public function __construct(
        private UserCommands $userCommands,
    ) {}

    public function create(CreateUserData $createUserData, UserActionContext $userActionContext): UserCommandResult
    {
        $userCommandResult = $this->userCommands->create($createUserData);
        $mutationContext = $userActionContext->mutation(
            action: 'create',
            user: $userCommandResult->user,
            changes: $userCommandResult->changes,
        );

        Event::dispatch(new UserManagementEvent($mutationContext));

        return $userCommandResult;
    }

    public function update(User $user, UpdateUserData $updateUserData, UserActionContext $userActionContext): UserCommandResult
    {
        $userCommandResult = $this->userCommands->update($user, $updateUserData);
        $mutationContext = $userActionContext->mutation(
            action: 'update',
            user: $userCommandResult->user,
            changes: $userCommandResult->changes,
        );

        Event::dispatch(new UserManagementEvent($mutationContext));

        return $userCommandResult;
    }

    public function delete(User $user, UserActionContext $userActionContext): void
    {
        $this->userCommands->delete($user);

        Event::dispatch(new UserManagementEvent($userActionContext->mutation(
            action: 'delete',
            user: $user,
        )));
    }
}
