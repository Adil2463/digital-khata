<?php

declare(strict_types=1);

namespace App\Modules\Users\Listeners;

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Users\Events\UserManagementEvent;
use App\Modules\Users\Notifications\UserManagementBroadcastNotification;
use Illuminate\Support\Facades\Notification;

final class SendUserManagementBroadcastNotification
{
    public function handle(UserManagementEvent $userManagementEvent): void
    {
        $mutationContext = $userManagementEvent->context;
        /** @var User $actor */
        $actor = $mutationContext->actor;
        $admins = User::query()
            ->where('role', UserRole::Admin)
            ->whereKeyNot($actor->id)
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send(
            $admins,
            UserManagementBroadcastNotification::fromMutationContext($mutationContext),
        );
    }
}
