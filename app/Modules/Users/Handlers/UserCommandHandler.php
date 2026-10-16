<?php

declare(strict_types=1);

namespace App\Modules\Users\Handlers;

use App\Models\User;
use App\Modules\Users\Commands\UserCommandResult;
use App\Modules\Users\Commands\UserCommands;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Enums\UsersRealtimeAction;
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
        return $this->announce(
            UsersRealtimeAction::Create,
            $this->userCommands->create($createUserData, $userActionContext->actor),
            $userActionContext,
        );
    }

    public function update(User $user, UpdateUserData $updateUserData, UserActionContext $userActionContext): UserCommandResult
    {
        return $this->announce(
            UsersRealtimeAction::Update,
            $this->userCommands->update($user, $updateUserData, $userActionContext->actor, $userActionContext->metadata->sessionId),
            $userActionContext,
        );
    }

    public function deactivate(User $user, UserActionContext $userActionContext): UserCommandResult
    {
        return $this->announce(
            UsersRealtimeAction::Deactivate,
            $this->userCommands->deactivate($user, $userActionContext->actor),
            $userActionContext,
        );
    }

    public function reactivate(User $user, UserActionContext $userActionContext): UserCommandResult
    {
        return $this->announce(
            UsersRealtimeAction::Reactivate,
            $this->userCommands->reactivate($user),
            $userActionContext,
        );
    }

    private function announce(UsersRealtimeAction $usersRealtimeAction, UserCommandResult $userCommandResult, UserActionContext $userActionContext): UserCommandResult
    {
        Event::dispatch(new UserManagementEvent($userActionContext->mutation(
            action: $usersRealtimeAction->value,
            user: $userCommandResult->user,
            changes: $userCommandResult->changes,
        )));

        return $userCommandResult;
    }
}
