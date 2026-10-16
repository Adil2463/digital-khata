<?php

declare(strict_types=1);

namespace App\Modules\Users\Listeners;

use App\Models\User;
use App\Modules\Shared\Data\UserViewData;
use App\Modules\Shared\Realtime\Contracts\RealtimeDispatcher;
use App\Modules\Users\Data\UserChangedBroadcastData;
use App\Modules\Users\Data\UsersListChangedBroadcastData;
use App\Modules\Users\Enums\UsersRealtimeAction;
use App\Modules\Users\Events\Broadcast\UserChanged;
use App\Modules\Users\Events\Broadcast\UsersListChanged;
use App\Modules\Users\Events\UserManagementEvent;

final readonly class BroadcastUserManagementRealtime
{
    public function __construct(
        private RealtimeDispatcher $realtimeDispatcher,
    ) {}

    public function handle(UserManagementEvent $userManagementEvent): void
    {
        $mutationContext = $userManagementEvent->context;
        /** @var User $actor */
        $actor = $mutationContext->actor;
        $usersRealtimeAction = $this->resolveAction($mutationContext->action);
        $occurredAt = $mutationContext->occurredAt();
        /** @var ?User $targetUser */
        $targetUser = $mutationContext->target;
        $targetUserId = $targetUser?->id;

        $this->realtimeDispatcher->dispatchToOthers(
            new UsersListChanged(new UsersListChangedBroadcastData(
                action: $usersRealtimeAction,
                actorUserId: $actor->id,
                targetUserId: $targetUserId,
                occurredAt: $occurredAt,
            )),
            $mutationContext->socketId(),
        );

        if ($targetUserId === null) {
            return;
        }

        /** @var User $targetUser */
        $userViewData = $targetUser->exists
            ? UserViewData::fromModel($targetUser)
            : null;

        $this->realtimeDispatcher->dispatchToOthers(
            new UserChanged(new UserChangedBroadcastData(
                action: $usersRealtimeAction,
                actorUserId: $actor->id,
                targetUserId: $targetUserId,
                user: $userViewData,
                occurredAt: $occurredAt,
            )),
            $mutationContext->socketId(),
        );
    }

    private function resolveAction(string $action): UsersRealtimeAction
    {
        return UsersRealtimeAction::from($action);
    }
}
